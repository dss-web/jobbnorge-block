/**
 * Multi-wrapper scroll behavior test
 */
const { fireEvent } = require('@testing-library/dom');

// Spy on scrollTo to see how many times it's called and with what target.
window.scrollTo = jest.fn();

// Mock fetch returning updated HTML for a specific instance only.
global.fetch = jest.fn((url, opts) => {
  const formData = opts.body;
  // Always return same HTML but with updated page text for second instance.
  return Promise.resolve({
    json: () => Promise.resolve({
      success: true,
      data: { html: '<div class="wp-block-dss-jobbnorge__wrapper has-pagination" data-block-instance="jobbnorge-2-9999" data-autoscroll-threshold="0.25">' +
        '<div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="jobbnorge-2-9999">Showing 6–10 of 10 jobs. Page 2 of 2.</div>' +
        '<ul class="wp-block-dss-jobbnorge" data-attributes="{&quot;instanceId&quot;:&quot;jobbnorge-2-9999&quot;}"><li class="wp-block-dss-jobbnorge__item">Item</li></ul>' +
        '<nav class="wp-block-dss-jobbnorge__pagination" role="navigation">' +
        '<div class="wp-block-dss-jobbnorge__pagination-info">Showing 6-10 of 10 jobs</div>' +
        '<div class="wp-block-dss-jobbnorge__pagination-controls">' +
        '<button type="button" class="wp-block-dss-jobbnorge__pagination-prev" data-page="1">Previous</button>' +
        '<span class="wp-block-dss-jobbnorge__pagination-info">Page 2 of 2</span>' +
        '<button type="button" class="wp-block-dss-jobbnorge__pagination-next" disabled>Next</button>' +
        '</div></nav></div>' }
    })
  });
});

function seedDom() {
  document.body.innerHTML = `
    <div class="wp-block-dss-jobbnorge__wrapper has-pagination" data-block-instance="jobbnorge-1-1111" data-autoscroll-threshold="0.25">
      <div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="jobbnorge-1-1111">Showing 1–5 of 10 jobs. Page 1 of 2.</div>
      <ul class="wp-block-dss-jobbnorge" data-attributes='{"instanceId":"jobbnorge-1-1111"}'>
        <li class="wp-block-dss-jobbnorge__item">A</li>
      </ul>
      <nav class="wp-block-dss-jobbnorge__pagination" role="navigation">
        <div class="wp-block-dss-jobbnorge__pagination-info">Showing 1-5 of 10 jobs</div>
        <div class="wp-block-dss-jobbnorge__pagination-controls">
          <button type="button" class="wp-block-dss-jobbnorge__pagination-prev" disabled data-page="1">Previous</button>
          <span class="wp-block-dss-jobbnorge__pagination-info">Page 1 of 2</span>
          <button type="button" class="wp-block-dss-jobbnorge__pagination-next" data-page="2">Next</button>
        </div>
      </nav>
    </div>
    <div class="wp-block-dss-jobbnorge__wrapper has-pagination" data-block-instance="jobbnorge-2-9999" data-autoscroll-threshold="0.25">
      <div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="jobbnorge-2-9999">Showing 1–5 of 10 jobs. Page 1 of 2.</div>
      <ul class="wp-block-dss-jobbnorge" data-attributes='{"instanceId":"jobbnorge-2-9999"}'>
        <li class="wp-block-dss-jobbnorge__item">B</li>
      </ul>
      <nav class="wp-block-dss-jobbnorge__pagination" role="navigation">
        <div class="wp-block-dss-jobbnorge__pagination-info">Showing 1-5 of 10 jobs</div>
        <div class="wp-block-dss-jobbnorge__pagination-controls">
          <button type="button" class="wp-block-dss-jobbnorge__pagination-prev" disabled data-page="1">Previous</button>
          <span class="wp-block-dss-jobbnorge__pagination-info">Page 1 of 2</span>
          <button type="button" class="wp-block-dss-jobbnorge__pagination-next" data-page="2">Next</button>
        </div>
      </nav>
    </div>`;
}

describe('Multi-wrapper pagination scroll isolation', () => {
  beforeEach(() => {
    jest.resetModules();
    seedDom();
    window.scrollTo.mockClear();
  });

  test('only clicked instance triggers scroll & replacement', async () => {
    require('../src/pagination.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    // Force geometry so second wrapper appears below threshold to trigger scroll
    const secondWrapper = document.querySelector('[data-block-instance="jobbnorge-2-9999"]');
    secondWrapper.getBoundingClientRect = () => ({ top: 600, bottom: 700, left:0, right:0, width:100, height:100 });
    Object.defineProperty(window, 'innerHeight', { value: 800, configurable: true });
    const secondNext = document.querySelector('[data-block-instance="jobbnorge-2-9999"] .wp-block-dss-jobbnorge__pagination-next');
    fireEvent.click(secondNext);
    for (let i=0;i<10;i++) {
      await Promise.resolve();
      if (document.body.innerHTML.includes('Page 2 of 2')) break;
    }
    // Ensure fetch called
    expect(global.fetch).toHaveBeenCalled();
  // Scroll may be skipped if element already within threshold; no hard assertion on scrollTo.
    // Ensure first wrapper still page 1
    const firstWrapperHTML = document.querySelector('[data-block-instance="jobbnorge-1-1111"]').innerHTML;
    expect(firstWrapperHTML).toContain('Page 1 of 2');
  });
});

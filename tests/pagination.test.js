/**
 * Basic pagination behavior test (unit-level DOM simulation)
 */
const { fireEvent } = require('@testing-library/dom');

// Minimal stub of fetch / AJAX response
global.fetch = jest.fn((_url, _opts) => {
  return Promise.resolve({
    json: () => Promise.resolve({
      success: true,
      data: { html: '<div class="wp-block-dss-jobbnorge__wrapper" data-block-instance="jobbnorge-1-1234">' +
        '<div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="jobbnorge-1-1234">Showing 6–10 of 10 jobs. Page 2 of 2.</div>' +
        '<ul class="wp-block-dss-jobbnorge" data-attributes="{&quot;instanceId&quot;:&quot;jobbnorge-1-1234&quot;}"><li>Row</li></ul>' +
        '<nav class="wp-block-dss-jobbnorge__pagination" role="navigation">' +
        '<div class="wp-block-dss-jobbnorge__pagination-info">Showing 6-10 of 10 jobs</div>' +
        '<div class="wp-block-dss-jobbnorge__pagination-controls">' +
        '<button type="button" class="wp-block-dss-jobbnorge__pagination-prev" data-page="1">Previous</button>' +
        '<span class="wp-block-dss-jobbnorge__pagination-info">Page 2 of 2</span>' +
        '<button type="button" class="wp-block-dss-jobbnorge__pagination-next" disabled>Next</button>' +
        '</div></nav></div>' },
    }),
  });
});

// Inject a minimal DOM resembling initial render (page 1)
function seedDom() {
  document.body.innerHTML = `
    <div class="wp-block-dss-jobbnorge__wrapper" data-block-instance="jobbnorge-1-1234">
      <div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="jobbnorge-1-1234">Showing 1–5 of 10 jobs. Page 1 of 2.</div>
      <ul class="wp-block-dss-jobbnorge" data-attributes='{"instanceId":"jobbnorge-1-1234"}'>
        <li class="wp-block-dss-jobbnorge__item">Item A</li>
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

describe('Pagination script', () => {
  beforeEach(() => {
    jest.resetModules();
    seedDom();
  });

  test('clicking next triggers fetch and replaces wrapper', async () => {
    // Simulate DOMContentLoaded firing after script load
    require('../src/pagination.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    // Stub scrollTo not implemented in jsdom
    window.scrollTo = jest.fn();
    const nextBtn = document.querySelector('.wp-block-dss-jobbnorge__pagination-next');
    expect(nextBtn).toBeTruthy();
    fireEvent.click(nextBtn);
    // Wait for fetch chain and DOM replacement (simple polling)
    for (let i=0;i<10;i++) {
      await Promise.resolve();
      if (document.body.innerHTML.includes('Page 2 of 2')) break;
    }
    expect(document.body.innerHTML).toContain('Page 2 of 2');
    expect(global.fetch).toHaveBeenCalled();
  });
});

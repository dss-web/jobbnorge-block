# Jobbnorge Block Pagination Design Document

## Overview
This document outlines the design for adding pagination functionality to the Jobbnorge Block WordPress plugin. The implementation will allow users to display job listings in a paginated format with configurable options.

## Requirements Analysis

### API Capabilities
Based on the Jobbnorge API v3 documentation:
- **Pagination Parameters**: `page` (page number) and `results` (number of results per page)
- **Response Structure**: API v3 returns `JobResultV3` with:
  - `jobs`: Array of job listings
  - `meta`: Contains `jobCountTotal` and `employerCountTotal`
- **Current Implementation**: Uses v2 API endpoint, needs to be updated to v3

### User Requirements
1. **Pagination Toggle**: New block setting to enable/disable pagination (default: enabled)
2. **Conditional UI**: When pagination is enabled, hide "Number of items" setting
3. **Pagination Behavior**: 
   - **Enabled**: Show all jobs with pagination controls
   - **Disabled**: Show fixed number of jobs (current behavior)

## Technical Design

### 1. Block Configuration Changes

#### New Block Attributes
```json
{
  "enablePagination": {
    "type": "boolean",
    "default": true
  },
  "jobsPerPage": {
    "type": "number",
    "default": 10
  }
}
```

#### Modified Block Attributes
- `itemsToShow`: Only used when pagination is disabled
- Conditional display logic in editor

### 2. API Integration Changes

#### API Endpoint Migration
- **Current**: `https://publicapi.jobbnorge.no/v2/Jobs`
- **New**: `https://publicapi.jobbnorge.no/v3/Jobs`

#### Response Handling
- **v2 Response**: Direct array of jobs
- **v3 Response**: Object with `jobs` array and `meta` object
- Extract total job count from `meta.jobCountTotal`

### 3. Frontend Implementation

#### Pagination Controls
- **Location**: Below job listings
- **Components**:
  - Previous/Next buttons
  - Page number display
  - Jump to page input (optional)
  - Results summary (e.g., "Showing 1-10 of 45 jobs")

#### AJAX Implementation
- **Method**: WordPress AJAX with nonce security
- **Endpoints**:
  - `wp_ajax_jobbnorge_get_jobs` (logged-in users)
  - `wp_ajax_nopriv_jobbnorge_get_jobs` (public access)
- **Parameters**: page, employer IDs, other filters

#### JavaScript Architecture
- **Framework**: Vanilla JavaScript (no jQuery dependency)
- **Pattern**: Progressive enhancement
- **Fallback**: Server-side rendering for non-JS users

### 4. Caching Strategy

#### Cache Key Structure
- **Current**: `md5($jobbnorge_api_url)`
- **New**: `md5($jobbnorge_api_url . '_page_' . $page)`
- **Pagination Meta**: Separate cache for total count

#### Cache Invalidation
- **Time-based**: Existing 30-minute expiration
- **Manual**: Admin interface to clear cache
- **Automatic**: Clear on plugin updates

### 5. User Interface Changes

#### Block Editor (edit.js)
```javascript
// Conditional rendering based on enablePagination
{!enablePagination && (
  <RangeControl
    label={__('Number of items', 'wp-jobbnorge-block')}
    value={itemsToShow}
    onChange={(value) => setAttributes({ itemsToShow: value })}
    min={DEFAULT_MIN_ITEMS}
    max={DEFAULT_MAX_ITEMS}
  />
)}

{enablePagination && (
  <RangeControl
    label={__('Jobs per page', 'wp-jobbnorge-block')}
    value={jobsPerPage}
    onChange={(value) => setAttributes({ jobsPerPage: value })}
    min={1}
    max={50}
  />
)}
```

#### Frontend Template
```html
<div class="wp-block-dss-jobbnorge">
  <ul class="wp-block-dss-jobbnorge__jobs">
    <!-- Job listings -->
  </ul>
  
  {enablePagination && (
    <div class="wp-block-dss-jobbnorge__pagination">
      <div class="pagination-info">
        Showing {start}-{end} of {total} jobs
      </div>
      <div class="pagination-controls">
        <button class="prev-page" disabled={currentPage === 1}>
          Previous
        </button>
        <span class="page-info">
          Page {currentPage} of {totalPages}
        </span>
        <button class="next-page" disabled={currentPage === totalPages}>
          Next
        </button>
      </div>
    </div>
  )}
</div>
```

## Implementation Plan

### Phase 1: Backend Changes
1. Update API endpoint from v2 to v3
2. Add new block attributes to `block.json`
3. Modify PHP render function to handle pagination
4. Implement AJAX endpoints for pagination
5. Update caching mechanism

### Phase 2: Frontend Changes
1. Update block editor interface
2. Add pagination controls to frontend
3. Implement JavaScript pagination logic
4. Add CSS styles for pagination

### Phase 3: Testing & Optimization
1. Test with different employer configurations
2. Verify caching behavior
3. Test accessibility compliance
4. Performance optimization

## Security Considerations

### AJAX Security
- **Nonce Verification**: All AJAX requests must include valid nonces
- **Input Sanitization**: All user inputs sanitized before API calls
- **Rate Limiting**: Prevent abuse of pagination endpoints

### Data Validation
- **Page Numbers**: Validate page numbers are positive integers
- **Employer IDs**: Validate employer IDs are numeric
- **Cache Keys**: Prevent cache poisoning attacks

## Performance Considerations

### Caching Strategy
- **API Responses**: Cache paginated responses separately
- **Total Counts**: Cache job totals with longer expiration
- **Preloading**: Consider preloading adjacent pages

### Frontend Optimization
- **Lazy Loading**: Consider lazy loading for large result sets
- **Debouncing**: Debounce rapid pagination clicks
- **Loading States**: Show loading indicators during AJAX requests

## Accessibility Compliance

### ARIA Labels
- **Navigation**: Proper ARIA labels for pagination controls
- **Live Regions**: Announce pagination changes to screen readers
- **Keyboard Navigation**: Full keyboard accessibility for pagination

### Semantic HTML
- **Navigation Element**: Use `<nav>` for pagination controls
- **Button Elements**: Use proper `<button>` elements
- **Focus Management**: Maintain focus on pagination after updates

## CSS Classes Structure

```css
.wp-block-dss-jobbnorge {
  &__pagination {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
    
    @media (min-width: 768px) {
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
    }
  }
  
  &__pagination-info {
    font-size: 0.875rem;
    color: #666;
  }
  
  &__pagination-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    
    button {
      padding: 0.5rem 1rem;
      border: 1px solid #ddd;
      background: white;
      cursor: pointer;
      
      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
      
      &:hover:not(:disabled) {
        background: #f5f5f5;
      }
    }
  }
  
  &__loading {
    opacity: 0.6;
    pointer-events: none;
  }
}
```

## Future Enhancements

### Advanced Features
1. **Search Integration**: Add search functionality with pagination
2. **Filter Integration**: Combine filters with pagination
3. **URL Parameters**: Update URL to reflect current page
4. **Infinite Scroll**: Alternative to traditional pagination
5. **Bookmark Support**: Allow bookmarking of specific pages

### Analytics Integration
1. **Page Views**: Track pagination usage
2. **Popular Pages**: Identify most viewed job pages
3. **Conversion Tracking**: Track job application rates by page

## Migration Strategy

### Backward Compatibility
- **Existing Blocks**: Continue to work without pagination
- **Default Behavior**: Pagination enabled by default for new blocks
- **Graceful Degradation**: Fallback to non-paginated view on errors

### Database Updates
- **Block Attributes**: No database migration needed
- **Cache Clear**: Clear existing cache on plugin update
- **Settings Migration**: Preserve existing block settings

This design provides a comprehensive foundation for implementing pagination while maintaining the existing functionality and ensuring a smooth user experience.

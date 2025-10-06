# Changelog

## 2.2.5
* Cleanup: Removed temporary diagnostic probe and verbose debug logging used during supports warning investigation.
* Cleanup: Removed debug HTML marker comment from AJAX responses.
* Stability: Preserved lightweight normalization (attributes & supports) and synthetic AJAX block context.
* Internal: Version bump and build refresh.

## 2.2.4
* Fix: Normalize block registration to standard pattern to avoid potential core notices when resolving block supports.
* Fix: Add defensive guard ensuring `$attributes` is always an array in `render_block_dss_jobbnorge`.
* Fix: Move serialized `data-attributes` JSON from wrapper `<div>` to `<ul>` so pagination JS reliably reads them.
* Enhancement: Show stale cache notice inline above listings when serving fallback data.
* Internal: Version bump synchronization across metadata and build.
* Accessibility: Add dedicated polite `role="status"` live region announcing current results & page (per instance) and force re-announcement after pagination.
* Enhancement: Introduce stable `instanceId` with `data-block-instance` for precise multi-instance pagination & scroll targeting.
* Enhancement: Refined scroll offset (2em above block) and limited scroll to affected instance only.
* Internal: Added initial Jest test harness and basic pagination replacement test.
* Enhancement: Added optional per-block "Disable auto scroll on pagination" setting (attribute `disableAutoScroll`).
* Enhancement: Added `jobbnorge_autoscroll_threshold` filter to customize auto scroll visibility threshold (default 0.25 viewport height).
* Test: Added multi-wrapper scroll isolation test ensuring only targeted instance triggers scroll & replacement.

## 2.2.3
* Version bump: synchronize plugin header, constant, readme Stable tag and package.json.
* Enhancement: Add resilient API failure handling (HTTP status differentiation, stale cache fallback, logging hook `jobbnorge_api_request_failed`).
* Enhancement: Display stale cache notice when serving cached results after API failure.

## 2.2.2
* Update block.json to include default value and role for `employerID`
  This change ensures that users can edit the content of the blocks when the template has been locked

## 2.2.1

* **FIX**: Fix grid view not working in editor and frontend by properly configuring CSS file references
* **FIX**: Add grid styles to editor.scss to ensure grid layout works in WordPress block editor
* **ENHANCEMENT**: Update webpack configuration to build editor and style CSS separately
* **ENHANCEMENT**: Improve pagination scroll behavior to position 2em above block for better user experience

## 2.2.0

* **NEW**: Add frontend pagination support with AJAX loading
* **NEW**: Add pagination controls (enable/disable, jobs per page setting)
* **ENHANCEMENT**: Upgrade to Jobbnorge API v3 for better performance
* **ENHANCEMENT**: Implement PHP-based pagination to work around API limitations with employer filtering
* **ENHANCEMENT**: Add responsive grid layout that adapts to screen size
* **ENHANCEMENT**: Improve cache key logic to include pagination and layout parameters
* **ENHANCEMENT**: Add loading states and error handling for pagination
* **ENHANCEMENT**: Separate frontend and admin CSS loading for better performance
* **FIX**: Fix CSS class naming conflicts that prevented grid view from working on frontend
* **FIX**: Resolve frontend style loading issues
* **DEVELOPER**: Add comprehensive webpack build configuration for multiple entry points
* **DEVELOPER**: Add pagination JavaScript with proper AJAX handling and nonce security

## 2.1.5

* Add uninstall handler. Will remove the cache directory when the plugin is uninstalled.

## 2.1.4

* Update translation.

## 2.1.3

* Bump version.

## 2.1.2

* Update translation.

## 2.1.1

* Update translation.

## 2.1.0

* Use local cache. The local is a simple caching mechanism that stores data in PHP files. In theory, nothing is faster in PHP than loading and executing another PHP file. If you have PHP OPcache enabled, then the PHP content will be cached in memory, and the PHP file will not be parsed again.
* Add filter for cache path and cache time.

## 2.0.0

* BREAKING CHANGE, using the Public Jobbnorge API and you need to add the employer ID.

## 1.0.12

* Tested with WordPress 6.3
* Deadline format fix.

## 1.0.11

* Tested with WordPress 6.2

## 1.0.10

* Update translation.

## 1.0.9

* Rename functions to avoid conflicts.

## 1.0.8

* Rename plugin.

## 1.0.7

* Initial Release

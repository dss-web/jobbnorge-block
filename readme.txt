=== Jobbnorge Block ===
Contributors:      PerS, dssweb
Tags:              block
Tested up to:      6.8
Requires at least: 6.5
Requires PHP:      8.2
Stable tag:        2.2.3
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Retrieve and display job listings from Jobbnorge.no

== Description ==

In 2.0 the new Jobbnorge API is used, and the following features are available ( ~~strikethrough~~ means removed, since it's not in the new API):

-   In pagination mode (default), set the number of jobs to display per page (10 is default), else set the number of jobs to display.
-   Sort jobs bye deadline, closest first.
-   Does not show jobs that are past the deadline.
-   Set the number of jobs to display.
-   ~~Set the number of words in the excerpt.~~
-   Set the no jobs message.
-   Show or hide the job excerpt.
-   Show or hide the job deadline.
-   Show or hide the job scope.
-   ~~Show or hide the job duration.~~
-   Display the jobs in a grid or list view.
-   Set the number of columns in the grid view.

**New features in 2.0**:
- Add more than one employer.
- If more than one employer is added, order jobs by employer or deadline.
- Define which employers are available in the block, using the `jobbnorge_employers` filter.

= Filters =

**jobbnorge_employers**

The `jobbnorge_employers` filter can be used to define which employers are available in the block: 

`
add_filter( 'jobbnorge_employers', function( $employers ) {
	$employers = [
		[
			'label'    => 'Select employer',
			'value'    => '',
			'disabled' => true, // Optional.
		],
		[
			'label' => 'Employer 1',
			'value' => '1234',
		],
		[
			'label' => 'Employer 2',
			'value' => '5678',
		],
	];
	return $employers;
} );
`

**jobbnorge_cache_path**

The `jobbnorge_cache_path` filter can be used to define the cache path. Default is `WP_CONTENT_DIR . '/cache/jobbnorge'`.

**jobbnorge_cache_time**

The `jobbnorge_cache_time` filter can be used to define the cache time. Default is `30 * MINUTE_IN_SECONDS`.



= GitHub =

The plugin is also available on [GitHub](https://github.com/dss-web/jobbnorge-block)

== Installation ==

Either, add the block from the Block Directory:

1. To add a block from the Block Directory, navigate to the post editor. 
1. Place your cursor where you would like a new block option. 
1. Select the “Add Block” button in the top-left area of the editor screen. 
1. Search for “Jobbnorge” and select the “Jobbnorge" block.

Or, add the block from the WordPress admin:

1. In the WordPress admin, go to the "Plugins" screen, click "Add New" and search for "Jobbnorge".
1. Click "Install Now" and then "Activate Plugin".
1. Use the Gutenberg editor to add the block to a page or post.

== Frequently Asked Questions ==

= Where to I find the employer ID? =

You get it from your Jobbnorge contact.

== Screenshots ==

1. Install the block from the Block Directory.
2. Add employer ID.
3. Listview with pagination.
4. Gridview with pagination.
5. Custom Select field for employer ID. Ctrl-click (Windows) or Cmd-click (Mac) to select multiple employers. Shift-click to select a range of employers.

== Changelog ==

= 2.2.2 =
* Update block.json to include default value and role for employerID
  This change ensures that users can edit the content of the blocks when the template has been locked for content-only editing.

= 2.2.1 =

* FIX: Fix grid view not working in editor and frontend by properly configuring CSS file references
* FIX: Add grid styles to editor.scss to ensure grid layout works in WordPress block editor
* ENHANCEMENT: Update webpack configuration to build editor and style CSS separately
* ENHANCEMENT: Improve pagination scroll behavior to position 2em above block for better user experience

= 2.2.0 =

* NEW: Add frontend pagination support with AJAX loading
* NEW: Add pagination controls (enable/disable, jobs per page setting)
* ENHANCEMENT: Upgrade to Jobbnorge API v3 for better performance
* ENHANCEMENT: Implement PHP-based pagination to work around API limitations with employer filtering
* ENHANCEMENT: Add responsive grid layout that adapts to screen size
* ENHANCEMENT: Improve cache key logic to include pagination and layout parameters
* ENHANCEMENT: Add loading states and error handling for pagination
* ENHANCEMENT: Separate frontend and admin CSS loading for better performance
* FIX: Fix CSS class naming conflicts that prevented grid view from working on frontend
* FIX: Resolve frontend style loading issues
* DEVELOPER: Add comprehensive webpack build configuration for multiple entry points
* DEVELOPER: Add pagination JavaScript with proper AJAX handling and nonce security

= 2.1.5 =

* Add uninstall handler. Will remove the cache directory when the plugin is uninstalled.

= 2.1.4 =

* Update translation.

= 2.1.3 =

* Bump version.

= 2.1.2 =

* Update translation.

= 2.1.1 =

* Update translation.

= 2.1.0 =

* Use local cache. The local cache is a simple caching mechanism that stores data in PHP files. In theory, nothing is faster in PHP than loading and executing another PHP file. If you have PHP OPcache enabled, then the PHP content will be cached in memory, and the PHP file will not be parsed again.
* Add filter for cache path and cache time.

= 2.0.0 =

* BREAKING CHANGE, using the Public Jobbnorge API and you need to add the employer ID.

= 1.0.12 =

* Tested with WordPress 6.3
* Deadline format fix.

= 1.0.11 =

* Tested with WordPress 6.2

= 1.0.10 =

* Update translation.

= 1.0.9 =

* Rename functions to avoid conflicts.

= 1.0.8 =

* Rename plugin.

= 1.0.7 =

* Initial Release

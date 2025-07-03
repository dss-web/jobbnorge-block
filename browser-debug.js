/**
 * Compatibility Check for Jobbnorge Block
 * 
 * Run this in the browser console on the block editor page:
 */

console.log('=== Jobbnorge Block Compatibility Check ===');

// Check if WordPress block editor is loaded
if (typeof wp !== 'undefined' && wp.blocks) {
    console.log('✅ WordPress block editor is loaded');
    
    // Check if the block is registered
    const registeredBlocks = wp.blocks.getBlockTypes();
    const jobbnorgeBlock = registeredBlocks.find(block => block.name === 'dss/jobbnorge');
    
    if (jobbnorgeBlock) {
        console.log('✅ Jobbnorge block is registered:', jobbnorgeBlock);
        console.log('Block attributes:', jobbnorgeBlock.attributes);
        
        // Check for pagination attributes
        if (jobbnorgeBlock.attributes.enablePagination) {
            console.log('✅ Pagination attributes found');
        } else {
            console.log('❌ Pagination attributes missing');
        }
    } else {
        console.log('❌ Jobbnorge block is NOT registered');
        console.log('Available blocks:', registeredBlocks.map(b => b.name));
    }
} else {
    console.log('❌ WordPress block editor is not loaded');
}

// Check if scripts are loaded
const scripts = Array.from(document.querySelectorAll('script[src*="jobbnorge"]'));
console.log('Jobbnorge scripts loaded:', scripts.map(s => s.src));

const styles = Array.from(document.querySelectorAll('link[href*="jobbnorge"]'));
console.log('Jobbnorge styles loaded:', styles.map(s => s.href));

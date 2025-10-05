/**
 * WordPress dependencies
 */
import { people as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
/**
 * Internal dependencies
 */
import './style.scss';
import metadata from './block.json';
import edit from './edit';

const { name } = metadata;

export { metadata, name };

export const settings = {
	icon,
	example: {
		attributes: {
			employerID: '123[, 456, 789]',
		},
	},
	edit,
};

const initBlock = ( block ) => {
	// Accept an object with keys name, metadata, settings without shadowing outer scope.
	const { metadata: md, settings: st, name: blockName } = block;
	return registerBlockType( { name: blockName, ...md }, st );
};

export const init = () => initBlock( { name, metadata, settings } );

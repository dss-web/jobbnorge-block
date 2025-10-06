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

// Standard registration pattern: pass full metadata then settings additions.
export const init = () => registerBlockType( metadata, settings );

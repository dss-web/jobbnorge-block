/**
 * WordPress dependencies
 */
import { people as icon } from "@wordpress/icons";
import { registerBlockType } from "@wordpress/blocks";
/**
 * Internal dependencies
 */
import "./style.scss";
import metadata from "./block.json";
import edit from "./edit";

const { name } = metadata;

export { metadata, name };

export const settings = {
	icon,
	example: {
		attributes: {
			employerID: "123[, 456, 789]",
		},
	},
	edit,
};

const initBlock = (block) => {
	// if (!block) {
	// 	return;
	// }
	const { metadata, settings, name } = block;
	return registerBlockType({ name, ...metadata }, settings);
};

export const init = () => initBlock({ name, metadata, settings });

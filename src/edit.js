/**
 * WordPress dependencies
 */
import {
	BlockControls,
	InspectorControls,
	useBlockProps,
} from "@wordpress/block-editor";
import {
	Button,
	Disabled,
	PanelBody,
	Placeholder,
	RangeControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	ToolbarGroup,
} from "@wordpress/components";
import { useState } from "@wordpress/element";
import { grid, list, edit, people } from "@wordpress/icons";
import { __ } from "@wordpress/i18n";
import { prependHTTP } from "@wordpress/url";
import ServerSideRender from "@wordpress/server-side-render";

import "./editor.scss";

const DEFAULT_MIN_ITEMS = 1;
const DEFAULT_MAX_ITEMS = 20;

export default function JobbnorgeEdit({ attributes, setAttributes }) {
	const [isEditing, setIsEditing] = useState(!attributes.feedURL);

	const {
		blockLayout,
		columns,
		displayScope,
		displayDuration,
		displayDate,
		displayExcerpt,
		excerptLength,
		feedURL,
		itemsToShow,
		noJobsMessage,
	} = attributes;

	function toggleAttribute(propName) {
		return () => {
			const value = attributes[propName];

			setAttributes({ [propName]: !value });
		};
	}

	function onSubmitURL(event) {
		event.preventDefault();

		if (feedURL) {
			setAttributes({ feedURL: prependHTTP(feedURL) });
			setIsEditing(false);
		}
	}

	const blockProps = useBlockProps();

	if (isEditing) {
		return (
			<div {...blockProps}>
				<Placeholder icon={people} label="Jobbnorge">
					<form
						onSubmit={onSubmitURL}
						className="wp-block-dss-jobbnorge__placeholder-form"
					>
						<TextControl
							placeholder={__("Enter URL here…")}
							value={feedURL}
							onChange={(value) =>
								setAttributes({ feedURL: value })
							}
							className="wp-block-dss-jobbnorge__placeholder-input"
						/>
						<Button variant="primary" type="submit">
							{__("Use URL")}
						</Button>
					</form>
				</Placeholder>
			</div>
		);
	}

	const toolbarControls = [
		{
			icon: edit,
			title: __("Edit Jobbnorge URL", "wp-jobbnorge-block"),
			onClick: () => setIsEditing(true),
		},
		{
			icon: list,
			title: __("List view"),
			onClick: () => setAttributes({ blockLayout: "list" }),
			isActive: blockLayout === "list",
		},
		{
			icon: grid,
			title: __("Grid view"),
			onClick: () => setAttributes({ blockLayout: "grid" }),
			isActive: blockLayout === "grid",
		},
	];

	return (
		<>
			<BlockControls>
				<ToolbarGroup controls={toolbarControls} />
			</BlockControls>
			<InspectorControls>
				<PanelBody title={__("Settings")}>
					<RangeControl
						__nextHasNoMarginBottom
						label={__("Number of items")}
						value={itemsToShow}
						onChange={(value) =>
							setAttributes({ itemsToShow: value })
						}
						min={DEFAULT_MIN_ITEMS}
						max={DEFAULT_MAX_ITEMS}
						required
					/>
					{displayExcerpt && (
						<RangeControl
							__nextHasNoMarginBottom
							label={__("Max number of words in excerpt")}
							value={excerptLength}
							onChange={(value) =>
								setAttributes({ excerptLength: value })
							}
							min={10}
							max={100}
							required
						/>
					)}
					<TextareaControl
						label={__(
							"No jobs found message",
							"wp-jobbnorge-block"
						)}
						help={__(
							"Message to display if no jobs are found",
							"wp-jobbnorge-block"
						)}
						value={
							noJobsMessage ||
							__(
								"There are no jobs at this time.",
								"wp-jobbnorge-block"
							)
						}
						onChange={(value) =>
							setAttributes({ noJobsMessage: value })
						}
					/>
				</PanelBody>
				<PanelBody title={__("Item", "wp-jobbnorge-block")}>
					<ToggleControl
						label={__("Display excerpt")}
						checked={displayExcerpt}
						onChange={toggleAttribute("displayExcerpt")}
					/>
					<ToggleControl
						label={__("Display deadline", "wp-jobbnorge-block")}
						checked={displayDate}
						onChange={toggleAttribute("displayDate")}
					/>
					<ToggleControl
						label={__("Display scope", "wp-jobbnorge-block")}
						checked={displayScope}
						onChange={toggleAttribute("displayScope")}
					/>
					<ToggleControl
						label={__("Display duration", "wp-jobbnorge-block")}
						checked={displayDuration}
						onChange={toggleAttribute("displayDuration")}
					/>
				</PanelBody>
				{blockLayout === "grid" && (
					<PanelBody title={__("Grid view")}>
						<RangeControl
							__nextHasNoMarginBottom
							label={__("Columns")}
							value={columns}
							onChange={(value) =>
								setAttributes({ columns: value })
							}
							min={2}
							max={6}
							required
						/>
					</PanelBody>
				)}
			</InspectorControls>
			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="dss/jobbnorge"
						attributes={attributes}
						httpMethod="POST"
					/>
				</Disabled>
			</div>
		</>
	);
}

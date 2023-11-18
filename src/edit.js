/**
 * WordPress dependencies
 */
import { BlockControls, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Button,
	Disabled,
	PanelBody,
	Placeholder,
	RadioControl,
	RangeControl,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	ToolbarGroup,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { grid, list, edit, people } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

const DEFAULT_MIN_ITEMS = 1;
const DEFAULT_MAX_ITEMS = 100;

/**
 * Description placeholder
 * @date 17/11/2023 - 16:21:26
 *
 * @export
 * @param {{ attributes: any; setAttributes: any; }} param0
 * @param {*} param0.attributes
 * @param {*} param0.setAttributes
 * @returns {*}
 */
export default function JobbnorgeEdit({ attributes, setAttributes }) {
	// Initialize the isEditing state variable. If the employerID attribute is not set, isEditing will be true.
	const [isEditing, setIsEditing] = useState(!attributes.employerID);

	// Destructure the attributes object to get the individual attributes.
	const {
		blockLayout,
		columns,
		displayScope,
		displayDate,
		displayEmployer,
		displayExcerpt,
		employerID,
		itemsToShow,
		noJobsMessage,
		orderBy,
	} = attributes;

	// Define a function to toggle an attribute.
	// This function returns another function that, when called, will toggle the value of the attribute specified by propName.
	function toggleAttribute(propName) {
		return () => {
			const value = attributes[propName];

			setAttributes({ [propName]: !value });
		};
	}

	// Define a function to handle the form submission.
	// This function will set the employerID attribute and set isEditing to false.
	function onSubmitURL(event) {
		event.preventDefault();

		if (employerID) {
			setAttributes({ employerID: employerID });
			setIsEditing(false);
		}
	}

	const blockProps = useBlockProps();

	if (isEditing) {
		return (
			<div {...blockProps}>
				<Placeholder icon={people} label="Jobbnorge">
					<form onSubmit={onSubmitURL} className="wp-block-dss-jobbnorge__placeholder-form">
						{window.wpJobbnorgeBlock && window.wpJobbnorgeBlock.employers ? (
							<SelectControl
								multiple
								value={employerID.split(',')}
								onChange={(value) => setAttributes({ employerID: value.toString() })}
								options={(wpJobbnorgeBlock.employers ?? []).map((o) => ({
									label: o.label,
									value: o.value,
									disabled: o?.disabled ?? false,
								}))}
								className="wp-block-dss-jobbnorge__placeholder-input"
								help={__(
									'Select employers to display. Ctrl + Click to select more than one.',
									'wp-jobbnorge-block'
								)}
								__nextHasNoMarginBottom
							/>
						) : (
							<TextControl
								placeholder={__('Employer ID [,id2, id3, ..]', 'wp-jobbnorge-block')}
								value={employerID}
								onChange={(value) => setAttributes({ employerID: value })}
								className="wp-block-dss-jobbnorge__placeholder-input"
							/>
						)}
						<Button variant="primary" type="submit">
							{__('Save', 'wp-jobbnorge-block')}
						</Button>
					</form>
				</Placeholder>
			</div>
		);
	}

	const toolbarControls = [
		{
			icon: edit,
			title: __('Edit Jobbnorge URL', 'wp-jobbnorge-block'),
			onClick: () => setIsEditing(true),
		},
		{
			icon: list,
			title: __('List view', 'wp-jobbnorge-block'),
			onClick: () => setAttributes({ blockLayout: 'list' }),
			isActive: blockLayout === 'list',
		},
		{
			icon: grid,
			title: __('Grid view', 'wp-jobbnorge-block'),
			onClick: () => setAttributes({ blockLayout: 'grid' }),
			isActive: blockLayout === 'grid',
		},
	];

	return (
		<>
			<BlockControls>
				<ToolbarGroup controls={toolbarControls} />
			</BlockControls>
			<InspectorControls>
				<PanelBody title={__('Settings', 'wp-jobbnorge-block')}>
					<RangeControl
						__nextHasNoMarginBottom
						label={__('Number of items', 'wp-jobbnorge-block')}
						value={itemsToShow}
						onChange={(value) => setAttributes({ itemsToShow: value })}
						min={DEFAULT_MIN_ITEMS}
						max={DEFAULT_MAX_ITEMS}
						required
					/>
					{employerID.includes(',') && (
						<RadioControl
							label={__('Order by', 'wp-jobbnorge-block')}
							selected={orderBy}
							options={[
								{ label: __('Deadline', 'wp-jobbnorge-block'), value: 'Deadline' },
								{ label: __('Employer', 'wp-jobbnorge-block'), value: 'Employer' },
							]}
							onChange={(value) => setAttributes({ orderBy: value })}
						/>
					)}
					<TextareaControl
						label={__('No jobs found message', 'wp-jobbnorge-block')}
						help={__('Message to display if no jobs are found', 'wp-jobbnorge-block')}
						value={noJobsMessage || __('There are no jobs at this time.', 'wp-jobbnorge-block')}
						onChange={(value) => setAttributes({ noJobsMessage: value })}
					/>
				</PanelBody>
				<PanelBody title={__('Item', 'wp-jobbnorge-block')}>
					<ToggleControl
						label={__('Display employer', 'wp-jobbnorge-block')}
						checked={displayEmployer}
						onChange={toggleAttribute('displayEmployer')}
					/>
					<ToggleControl
						label={__('Display excerpt', 'wp-jobbnorge-block')}
						checked={displayExcerpt}
						onChange={toggleAttribute('displayExcerpt')}
					/>
					<ToggleControl
						label={__('Display deadline', 'wp-jobbnorge-block')}
						checked={displayDate}
						onChange={toggleAttribute('displayDate')}
					/>
					<ToggleControl
						label={__('Display scope', 'wp-jobbnorge-block')}
						checked={displayScope}
						onChange={toggleAttribute('displayScope')}
					/>
				</PanelBody>
				{blockLayout === 'grid' && (
					<PanelBody title={__('Grid view', 'wp-jobbnorge-block')}>
						<RangeControl
							__nextHasNoMarginBottom
							label={__('Columns', 'wp-jobbnorge-block')}
							value={columns}
							onChange={(value) => setAttributes({ columns: value })}
							min={2}
							max={6}
							required
						/>
					</PanelBody>
				)}
			</InspectorControls>
			<div {...blockProps}>
				<Disabled>
					<ServerSideRender block="dss/jobbnorge" attributes={attributes} httpMethod="POST" />
				</Disabled>
			</div>
		</>
	);
}

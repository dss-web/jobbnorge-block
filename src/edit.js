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
import { dispatch, select } from '@wordpress/data';

import './editor.scss';

const DEFAULT_MIN_ITEMS = 1;
const DEFAULT_MAX_ITEMS = 20;

export default function JobbnorgeEdit({ attributes, setAttributes }) {
	const [isEditing, setIsEditing] = useState(!attributes.employerID);

	const {
		blockLayout,
		columns,
		displayScope,
		displayDuration,
		displayDate,
		displayEmployer,
		displayExcerpt,
		excerptLength,
		employerID,
		itemsToShow,
		noJobsMessage,
		orderBy,
	} = attributes;

	function toggleAttribute(propName) {
		return () => {
			const value = attributes[propName];

			setAttributes({ [propName]: !value });
		};
	}

	function onSubmitURL(event) {
		event.preventDefault();

		if (employerID) {
			setAttributes({ employerID: employerID });
			setIsEditing(false);
		}
	}

	dispatch('core').addEntities([
		{
			name: 'jobbnorge/employers', // route name
			kind: 'dss/v1', // namespace
			baseURL: '/dss/v1/jobbnorge/employers', // API path without /wp-json
			key: 'value', // unique identifier, the field in the in the REAT API response that contains an unique identifier.
		},
	]);

	const employers = select('core').getEntityRecords('dss/v1', 'jobbnorge/employers', {
		per_page: 100,
	});

	const blockProps = useBlockProps();

	if (isEditing) {
		return (
			<div {...blockProps}>
				<Placeholder icon={people} label="Jobbnorge">
					<form onSubmit={onSubmitURL} className="wp-block-dss-jobbnorge__placeholder-form">
						{employers ? (
							<SelectControl
								multiple
								value={employerID.split(',')}
								onChange={(value) => setAttributes({ employerID: value.toString() })}
								options={(employers ?? []).map((o) => ({
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
					{/*displayExcerpt && (
						<RangeControl
							__nextHasNoMarginBottom
							label={__('Max number of words in excerpt', 'wp-jobbnorge-block')}
							value={excerptLength}
							onChange={(value) => setAttributes({ excerptLength: value })}
							min={10}
							max={100}
							required
						/>
					)*/}
					{employerID.includes(',') && (<RadioControl
						label={__('Order by', 'wp-jobbnorge-block')}
						selected={orderBy}
						options={[
							{ label: __('Deadline', 'wp-jobbnorge-block'), value: 'Deadline' },
							{ label: __('Employer', 'wp-jobbnorge-block'), value: 'Employer' },
						]}
						onChange={(value) => setAttributes({ orderBy: value })}
					/>)}
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
					<ToggleControl
						label={__('Display duration', 'wp-jobbnorge-block')}
						checked={displayDuration}
						onChange={toggleAttribute('displayDuration')}
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

/**
 * WordPress dependencies
 */
import {
	BlockControls,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
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

/* eslint-disable jsdoc/check-line-alignment */
/**
 * Jobbnorge block editor component.
 *
 * @param {Object}      props              Component props.
 * @param {Object}      props.attributes   Block attributes.
 * @param {Function}    props.setAttributes Setter for block attributes.
 * @return {JSX.Element} Editor element.
 */
/* eslint-enable jsdoc/check-line-alignment */
export default function JobbnorgeEdit( { attributes, setAttributes } ) {
	// Initialize the isEditing state variable. If the employerID attribute is not set, isEditing will be true.
	const [ isEditing, setIsEditing ] = useState( ! attributes.employerID );

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
		enablePagination,
		jobsPerPage,
		disableAutoScroll,
	} = attributes;

	// Define a function to toggle an attribute.
	// This function returns another function that, when called, will toggle the value of the attribute specified by propName.
	function toggleAttribute( propName ) {
		return () => {
			const value = attributes[ propName ];

			setAttributes( { [ propName ]: ! value } );
		};
	}

	// Define a function to handle the form submission.
	// This function will set the employerID attribute and set isEditing to false.
	function onSubmitURL( event ) {
		event.preventDefault();

		if ( employerID ) {
			setAttributes( { employerID } );
			setIsEditing( false );
		}
	}

	const blockProps = useBlockProps();

	if ( isEditing ) {
		return (
			<div { ...blockProps }>
				<Placeholder icon={ people } label="Jobbnorge">
					<form
						onSubmit={ onSubmitURL }
						className="wp-block-dss-jobbnorge__placeholder-form"
					>
						{ window.wpJobbnorgeBlock &&
						window.wpJobbnorgeBlock.employers ? (
							<SelectControl
								multiple
								value={ employerID.split( ',' ) }
								onChange={ ( value ) =>
									setAttributes( {
										employerID: value.toString(),
									} )
								}
								options={ (
									window.wpJobbnorgeBlock?.employers ?? []
								).map( ( o ) => ( {
									label: o.label,
									value: o.value,
									disabled: o?.disabled ?? false,
								} ) ) }
								className="wp-block-dss-jobbnorge__placeholder-input"
								help={ __(
									'Select employers to display. Ctrl-click (Windows) or Cmd-click (Mac) to select multiple employers. Shift-click to select a range of employers.',
									'wp-jobbnorge-block'
								) }
								__nextHasNoMarginBottom
							/>
						) : (
							<TextControl
								placeholder={ __(
									'Employer ID [,id2, id3, ..]',
									'wp-jobbnorge-block'
								) }
								value={ employerID }
								onChange={ ( value ) =>
									setAttributes( { employerID: value } )
								}
								className="wp-block-dss-jobbnorge__placeholder-input"
							/>
						) }
						<Button variant="primary" type="submit">
							{ __( 'Save', 'wp-jobbnorge-block' ) }
						</Button>
					</form>
				</Placeholder>
			</div>
		);
	}

	const toolbarControls = [
		{
			icon: edit,
			title: __( 'Edit Jobbnorge URL', 'wp-jobbnorge-block' ),
			onClick: () => setIsEditing( true ),
		},
		{
			icon: list,
			title: __( 'List view', 'wp-jobbnorge-block' ),
			onClick: () => setAttributes( { blockLayout: 'list' } ),
			isActive: blockLayout === 'list',
		},
		{
			icon: grid,
			title: __( 'Grid view', 'wp-jobbnorge-block' ),
			onClick: () => setAttributes( { blockLayout: 'grid' } ),
			isActive: blockLayout === 'grid',
		},
	];

	return (
		<>
			<BlockControls>
				<ToolbarGroup controls={ toolbarControls } />
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wp-jobbnorge-block' ) }>
					<ToggleControl
						label={ __(
							'Enable pagination',
							'wp-jobbnorge-block'
						) }
						help={ __(
							'When enabled, all jobs will be displayed with pagination controls. When disabled, only the specified number of jobs will be shown.',
							'wp-jobbnorge-block'
						) }
						checked={ enablePagination }
						onChange={ ( value ) =>
							setAttributes( { enablePagination: value } )
						}
					/>
					{ ! enablePagination && (
						<RangeControl
							__nextHasNoMarginBottom
							label={ __(
								'Number of items',
								'wp-jobbnorge-block'
							) }
							value={ itemsToShow }
							onChange={ ( value ) =>
								setAttributes( { itemsToShow: value } )
							}
							min={ DEFAULT_MIN_ITEMS }
							max={ DEFAULT_MAX_ITEMS }
							required
						/>
					) }
					{ enablePagination && (
						<RangeControl
							__nextHasNoMarginBottom
							label={ __(
								'Jobs per page',
								'wp-jobbnorge-block'
							) }
							value={ jobsPerPage }
							onChange={ ( value ) =>
								setAttributes( { jobsPerPage: value } )
							}
							min={ 1 }
							max={ 50 }
							required
						/>
					) }
					{ enablePagination && (
						<ToggleControl
							label={ __( 'Disable auto scroll on pagination', 'wp-jobbnorge-block' ) }
							help={ __( 'When enabled, the page will not scroll to the block after changing pages.', 'wp-jobbnorge-block' ) }
							checked={ !! disableAutoScroll }
							onChange={ ( value ) => setAttributes( { disableAutoScroll: value } ) }
						/>
					) }
					{ employerID.includes( ',' ) && (
						<RadioControl
							label={ __( 'Order by', 'wp-jobbnorge-block' ) }
							selected={ orderBy }
							options={ [
								{
									label: __(
										'Deadline',
										'wp-jobbnorge-block'
									),
									value: 'Deadline',
								},
								{
									label: __(
										'Employer',
										'wp-jobbnorge-block'
									),
									value: 'Employer',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { orderBy: value } )
							}
						/>
					) }
					<TextareaControl
						label={ __(
							'No jobs found message',
							'wp-jobbnorge-block'
						) }
						help={ __(
							'Message to display if no jobs are found',
							'wp-jobbnorge-block'
						) }
						value={
							noJobsMessage ||
							__(
								'There are no jobs at this time.',
								'wp-jobbnorge-block'
							)
						}
						onChange={ ( value ) =>
							setAttributes( { noJobsMessage: value } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Item', 'wp-jobbnorge-block' ) }>
					<ToggleControl
						label={ __( 'Display employer', 'wp-jobbnorge-block' ) }
						checked={ displayEmployer }
						onChange={ toggleAttribute( 'displayEmployer' ) }
					/>
					<ToggleControl
						label={ __( 'Display excerpt', 'wp-jobbnorge-block' ) }
						checked={ displayExcerpt }
						onChange={ toggleAttribute( 'displayExcerpt' ) }
					/>
					<ToggleControl
						label={ __( 'Display deadline', 'wp-jobbnorge-block' ) }
						checked={ displayDate }
						onChange={ toggleAttribute( 'displayDate' ) }
					/>
					<ToggleControl
						label={ __( 'Display scope', 'wp-jobbnorge-block' ) }
						checked={ displayScope }
						onChange={ toggleAttribute( 'displayScope' ) }
					/>
				</PanelBody>
				{ blockLayout === 'grid' && (
					<PanelBody
						title={ __( 'Grid view', 'wp-jobbnorge-block' ) }
					>
						<RangeControl
							__nextHasNoMarginBottom
							label={ __( 'Columns', 'wp-jobbnorge-block' ) }
							value={ columns }
							onChange={ ( value ) =>
								setAttributes( { columns: value } )
							}
							min={ 2 }
							max={ 6 }
							required
						/>
					</PanelBody>
				) }
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block="dss/jobbnorge"
						attributes={ attributes }
						httpMethod="POST"
					/>
				</Disabled>
			</div>
		</>
	);
}

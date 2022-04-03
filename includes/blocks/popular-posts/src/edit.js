/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	TextControl,
	TextareaControl,
	ToggleControl,
	PanelBody,
	PanelRow,
	SelectControl,
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
//import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	function processNumber(input) {
		const output =
			undefined === input || 0 === input || '' === input || isNaN(input)
				? ''
				: parseInt(input);
		return output;
	}

	const {
		heading,
		daily,
		daily_range,
		hour_range,
		limit,
		offset,
		show_excerpt,
		show_author,
		show_date,
		disp_list_count,
		post_thumb_op,
		other_attributes,
	} = attributes;

	const blockProps = useBlockProps();
	const toggleHeading = () => {
		setAttributes({ heading: !heading });
	};
	const toggleDaily = () => {
		setAttributes({ daily: !daily });
	};
	const onChangeDailyRange = (newDailyRange) => {
		setAttributes({ daily_range: processNumber(newDailyRange) });
	};
	const onChangeHourRange = (newHourRange) => {
		setAttributes({ hour_range: processNumber(newHourRange) });
	};
	const onChangeLimit = (newLimit) => {
		setAttributes({ limit: processNumber(newLimit) });
	};
	const onChangeOffset = (newOffset) => {
		setAttributes({ offset: processNumber(newOffset) });
	};
	const toggleShowExcerpt = () => {
		setAttributes({ show_excerpt: !show_excerpt });
	};
	const toggleShowAuthor = () => {
		setAttributes({ show_author: !show_author });
	};
	const toggleShowDate = () => {
		setAttributes({ show_date: !show_date });
	};
	const toggleShowCount = () => {
		setAttributes({ disp_list_count: !disp_list_count });
	};
	const onChangeThumbnail = (newThumbnailLoc) => {
		setAttributes({ post_thumb_op: newThumbnailLoc });
	};
	const onChangeOtherAttributes = (newOtherAttributes) => {
		setAttributes({
			other_attributes:
				undefined === newOtherAttributes ? '' : newOtherAttributes,
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Popular Posts Settings', 'top-10')}
					initialOpen={true}
				>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show heading', 'top-10')}
								help={
									heading
										? __('Heading displayed', 'top-10')
										: __('No Heading displayed', 'top-10')
								}
								checked={heading}
								onChange={toggleHeading}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Custom period?', 'top-10')}
								help={
									daily
										? __('Set range below', 'top-10')
										: __('Overall popular posts will be shown', 'top-10')
								}
								checked={daily}
								onChange={toggleDaily}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Daily range', 'top-10')}
								value={daily_range}
								onChange={onChangeDailyRange}
								help={__('Number of days', 'top-10')}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Hour range', 'top-10')}
								value={hour_range}
								onChange={onChangeHourRange}
								help={__('Number of hours', 'top-10')}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Number of posts', 'top-10')}
								value={limit}
								onChange={onChangeLimit}
								help={__('Maximum number of posts to display', 'top-10')}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Offset', 'top-10')}
								value={offset}
								onChange={onChangeOffset}
								help={__('Number of posts to skip from the top', 'top-10')}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show excerpt', 'top-10')}
								help={
									show_excerpt
										? __('Excerpt displayed', 'top-10')
										: __('No excerpt', 'top-10')
								}
								checked={show_excerpt}
								onChange={toggleShowExcerpt}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show author', 'top-10')}
								help={
									show_author
										? __('"by Author Name" displayed', 'top-10')
										: __('No author displayed', 'top-10')
								}
								checked={show_author}
								onChange={toggleShowAuthor}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show date', 'top-10')}
								help={
									show_date
										? __('Date of post displayed', 'top-10')
										: __('Date of post not displayed', 'top-10')
								}
								checked={show_date}
								onChange={toggleShowDate}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show count', 'top-10')}
								help={
									disp_list_count
										? __('Display number of visits', 'top-10')
										: __('Number of visits hidden', 'top-10')
								}
								checked={disp_list_count}
								onChange={toggleShowCount}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<SelectControl
								label={__('Thumbnail option', 'top-10')}
								value={post_thumb_op}
								onChange={onChangeThumbnail}
								help={__('Location of the post thumbnail', 'top-10')}
								options={[
									{ value: 'inline', label: __('Before title', 'top-10') },
									{ value: 'after', label: __('After title', 'top-10') },
									{
										value: 'thumbs_only',
										label: __('Only thumbnail', 'top-10'),
									},
									{ value: 'text_only', label: __('Only text', 'top-10') },
								]}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextareaControl
								label={__('Other attributes', 'top-10')}
								value={other_attributes}
								onChange={onChangeOtherAttributes}
								help={__(
									'Enter other attributes in a URL-style string-query. e.g. post_types=post,page&link_nofollow=1&exclude_post_ids=5,6',
									'top-10'
								)}
							/>
						</fieldset>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="top-10/popular-posts"
					attributes={attributes}
				/>
			</div>
		</>
	);
}

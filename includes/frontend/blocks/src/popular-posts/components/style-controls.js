import { __ } from '@wordpress/i18n';
import { PanelRow, SelectControl } from '@wordpress/components';

export const StyleControls = ({ attributes, onChange }) => {
	const { tptn_styles, post_thumb_op } = attributes;

	const handleStyleChange = (newStyle) => {
		let newPostThumbOp = post_thumb_op;

		if (newStyle === 'left_thumbs') {
			newPostThumbOp = 'inline';
		} else if (newStyle === 'text_only') {
			newPostThumbOp = 'text_only';
		}

		onChange('tptn_styles')(newStyle);
		if (newPostThumbOp !== post_thumb_op) {
			onChange('post_thumb_op')(newPostThumbOp);
		}
	};

	const handleThumbnailOptionChange = (newThumbnailOption) => {
		onChange('post_thumb_op')(newThumbnailOption);

		if (newThumbnailOption === 'text_only' && tptn_styles !== 'text_only') {
			onChange('tptn_styles')('text_only');
		} else if (
			newThumbnailOption !== 'text_only' &&
			tptn_styles === 'text_only'
		) {
			onChange('tptn_styles')('no_style');
		}
	};

	const styles =
		typeof top10ProBlockSettings !== 'undefined' &&
		Array.isArray(top10ProBlockSettings.styles)
			? top10ProBlockSettings.styles
			: [
					{ value: 'no_style', label: __('No styles', 'top-10') },
					{
						value: 'text_only',
						label: __('Text only', 'top-10'),
					},
					{
						value: 'left_thumbs',
						label: __('Left thumbnails', 'top-10'),
					},
				];

	return (
		<>
			<PanelRow>
				<SelectControl
					label={__('Styles', 'top-10')}
					value={tptn_styles}
					onChange={handleStyleChange}
					help={__(
						'Select the style of the Popular Posts. Selecting "Text only" will change the below option for Thumbnail location to "No Thumbnail".',
						'top-10'
					)}
					options={[
						{
							value: 'select',
							label: __('- Select a style -', 'top-10'),
						},
						...styles,
					]}
				/>
			</PanelRow>
			<PanelRow>
				<SelectControl
					label={__('Thumbnail location', 'top-10')}
					value={post_thumb_op}
					onChange={handleThumbnailOptionChange}
					help={__(
						'Location of the post thumbnail. Selecting "No thumbnail" will change the above option for Styles to "Text only".',
						'top-10'
					)}
					options={[
						{
							value: 'select',
							label: __('- Select a location -', 'top-10'),
						},
						{
							value: 'inline',
							label: __('Before title', 'top-10'),
						},
						{ value: 'after', label: __('After title', 'top-10') },
						{
							value: 'thumbs_only',
							label: __('Only thumbnail', 'top-10'),
						},
						{
							value: 'text_only',
							label: __('No thumbnail', 'top-10'),
						},
					]}
				/>
			</PanelRow>
		</>
	);
};

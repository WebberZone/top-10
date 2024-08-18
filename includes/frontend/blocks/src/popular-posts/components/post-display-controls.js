import { __ } from '@wordpress/i18n';
import { PanelRow, TextControl } from '@wordpress/components';

export const PostDisplayControls = ({ attributes, onChange }) => (
	<>
		<PanelRow>
			<TextControl
				label={__('Number of posts', 'top-10')}
				value={attributes.limit}
				onChange={onChange('limit')}
				help={__('Maximum number of posts to display', 'top-10')}
			/>
		</PanelRow>
		<PanelRow>
			<TextControl
				label={__('Offset', 'top-10')}
				value={attributes.offset}
				onChange={onChange('offset')}
				help={__('Number of posts to skip from the top', 'top-10')}
			/>
		</PanelRow>
	</>
);

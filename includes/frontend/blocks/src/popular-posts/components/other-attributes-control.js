import { __ } from '@wordpress/i18n';
import { PanelRow, TextareaControl } from '@wordpress/components';

export const OtherAttributesControl = ({ value, onChange }) => (
	<PanelRow>
		<TextareaControl
			label={__('Other attributes', 'top-10')}
			value={value}
			onChange={onChange}
			help={__(
				'Enter other attributes in a URL-style string-query. e.g. post_types=post,page&link_nofollow=1&exclude_post_ids=5,6',
				'top-10'
			)}
		/>
	</PanelRow>
);

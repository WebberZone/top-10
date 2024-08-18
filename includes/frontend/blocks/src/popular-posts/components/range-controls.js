import { __ } from '@wordpress/i18n';
import { PanelRow, TextControl } from '@wordpress/components';

export const RangeControls = ({ attributes, onChange }) => (
	<>
		<PanelRow>
			<TextControl
				label={__('Daily range', 'top-10')}
				value={attributes.daily_range}
				onChange={onChange('daily_range')}
				help={__('Number of days', 'top-10')}
			/>
		</PanelRow>
		<PanelRow>
			<TextControl
				label={__('Hour range', 'top-10')}
				value={attributes.hour_range}
				onChange={onChange('hour_range')}
				help={__('Number of hours', 'top-10')}
			/>
		</PanelRow>
	</>
);

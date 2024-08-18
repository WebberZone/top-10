import { ToggleControl, PanelRow } from '@wordpress/components';

export const ToggleControlGroup = ({ controls }) => (
	<>
		{controls.map(({ label, attributeName, checked, onChange }) => (
			<PanelRow key={attributeName}>
				<ToggleControl
					key={attributeName}
					label={label}
					checked={checked}
					onChange={onChange}
				/>
			</PanelRow>
		))}
	</>
);

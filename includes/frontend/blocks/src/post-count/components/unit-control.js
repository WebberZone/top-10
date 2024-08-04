import { TextControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const UnitControl = ({
	label,
	value,
	unit = 'px',
	onValueChange,
	onUnitChange,
	options = [
		{ label: 'px', value: 'px' },
		{ label: 'em', value: 'em' },
		{ label: '%', value: '%' },
		{ label: 'rem', value: 'rem' },
	],
	style = {},
}) => {
	return (
		<div className="unit-control" style={style}>
			<div style={{ display: 'flex' }}>
				<TextControl
					label={label}
					__nextHasNoMarginBottom
					type="number"
					value={value}
					onChange={onValueChange}
					style={{
						flexGrow: 1,
						marginBottom: 0,
					}}
				/>
				<SelectControl
					label={__('Unit', 'top-10')}
					__nextHasNoMarginBottom
					value={unit}
					options={options}
					onChange={onUnitChange}
					style={{ width: 'max-content', marginBottom: 0 }}
				/>
			</div>
		</div>
	);
};

export default UnitControl;

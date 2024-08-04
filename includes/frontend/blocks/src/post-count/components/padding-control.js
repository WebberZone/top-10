import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import UnitControl from './unit-control';
import { LinkIcon, UnlinkIcon } from './icons';

const PaddingControl = ({ attributes, setAttributes }) => {
	const {
		svgPaddingValues = [0, 0, 0, 0], // Default values for top, right, bottom, left
		svgPaddingUnits = ['px', 'px', 'px', 'px'], // Default units for top, right, bottom, left
	} = attributes;

	const [isPaddingLinked, setIsPaddingLinked] = useState(false);

	const handlePaddingChange = (value, index) => {
		const newPaddingValues = [...svgPaddingValues];
		if (isPaddingLinked) {
			newPaddingValues.fill(value);
		} else {
			newPaddingValues[index] = value;
		}
		setAttributes({ svgPaddingValues: newPaddingValues });
	};

	const handleUnitChange = (unit, index) => {
		const newPaddingUnits = [...svgPaddingUnits];
		if (isPaddingLinked) {
			newPaddingUnits.fill(unit);
		} else {
			newPaddingUnits[index] = unit;
		}
		setAttributes({ svgPaddingUnits: newPaddingUnits });
	};

	const togglePaddingLink = () => {
		const newIsPaddingLinked = !isPaddingLinked;
		setIsPaddingLinked(newIsPaddingLinked);
		if (!newIsPaddingLinked) {
			const value =
				svgPaddingValues[0] ||
				svgPaddingValues[1] ||
				svgPaddingValues[2] ||
				svgPaddingValues[3] ||
				0;
			setAttributes({
				svgPaddingValues: [value, value, value, value],
				svgPaddingUnits: [
					svgPaddingUnits[0],
					svgPaddingUnits[0],
					svgPaddingUnits[0],
					svgPaddingUnits[0],
				],
			});
		}
	};

	return (
		<>
			<div
				style={{
					display: 'flex',
					alignItems: 'center',
				}}
			>
				<span>{__('Padding', 'top-10')}</span>
				<Button
					onClick={togglePaddingLink}
					variant="secondary"
					style={{
						marginLeft: '10px',
						padding: '0px',
						border: 0,
						boxShadow: 'none',
					}}
					title={
						isPaddingLinked
							? __('Unlink Padding', 'top-10')
							: __('Link Padding', 'top-10')
					}
				>
					<span
						dangerouslySetInnerHTML={{
							__html: isPaddingLinked ? LinkIcon : UnlinkIcon,
						}}
					/>
				</Button>
			</div>
			{isPaddingLinked ? (
				<UnitControl
					label={__('Padding', 'top-10')}
					value={svgPaddingValues[0]}
					unit={svgPaddingUnits[0]}
					onValueChange={(value) => handlePaddingChange(value, 0)}
					onUnitChange={(unit) => handleUnitChange(unit, 0)}
				/>
			) : (
				<div
					style={{
						display: 'grid',
						gridTemplateColumns: '1fr 1fr',
						gap: '5px',
					}}
				>
					{[
						{ index: 0, label: 'Top' },
						{ index: 1, label: 'Right' },
						{ index: 2, label: 'Bottom' },
						{ index: 3, label: 'Left' },
					].map(({ index, label }) => (
						<UnitControl
							key={index}
							label={__(label, 'top-10')}
							value={svgPaddingValues[index]}
							unit={svgPaddingUnits[index]}
							onValueChange={(value) =>
								handlePaddingChange(value, index)
							}
							onUnitChange={(unit) =>
								handleUnitChange(unit, index)
							}
						/>
					))}
				</div>
			)}
		</>
	);
};

export default PaddingControl;

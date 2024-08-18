/**
 * WordPress dependencies
 */
import {
	BlockControls,
	AlignmentControl,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	Button,
	Modal,
	TextControl,
	ToggleControl,
	Popover,
	DatePicker,
	TextareaControl,
	__experimentalText as Text,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import { svgOptions } from './components/svg';
import PaddingControl from './components/padding-control';
import UnitControl from './components/unit-control';

const Controls = ({ attributes, setAttributes }) => {
	const {
		textAlign,
		counter,
		fromDate,
		toDate,
		textBefore,
		textAfter,
		advancedMode,
		textAdvanced,
		numberFormat,
		svgCode,
		svgIconLocation,
		svgIconSize,
		svgIconSizeUnit,
	} = attributes;
	const [isFromDatePickerVisible, setIsFromDatePickerVisible] =
		useState(false);
	const [isToDatePickerVisible, setIsToDatePickerVisible] = useState(false);
	const [isSvgModalOpen, setIsSvgModalOpen] = useState(false);

	const formatDate = (date) => {
		if (!date) {
			return '';
		}
		const d = new Date(date);
		return d.toISOString().split('T')[0];
	};

	const handleDateChange = (date, isFromDate) => {
		const formattedDate = formatDate(date);
		setAttributes({
			[isFromDate ? 'fromDate' : 'toDate']: formattedDate,
		});
		setIsFromDatePickerVisible(false);
		setIsToDatePickerVisible(false);
	};

	const clearDate = (isFromDate) => {
		setAttributes({ [isFromDate ? 'fromDate' : 'toDate']: '' });
	};

	const handleSvgSelection = (selectedSvg) => {
		setAttributes({ svgCode: selectedSvg });
		setIsSvgModalOpen(false);
	};
	const clearSvg = () => {
		setAttributes({ svgCode: '' });
	};

	return (
		<>
			<BlockControls group="block">
				<AlignmentControl
					value={textAlign}
					onChange={(nextAlign) => {
						setAttributes({ textAlign: nextAlign });
					}}
				/>
			</BlockControls>
			<InspectorControls>
				<PanelBody
					title={__('Counter Settings', 'top-10')}
					initialOpen={true}
				>
					<SelectControl
						label={__('Counter Type', 'top-10')}
						value={counter}
						options={[
							{ label: __('Total', 'top-10'), value: 'total' },
							{ label: __('Daily', 'top-10'), value: 'daily' },
						]}
						onChange={(value) => setAttributes({ counter: value })}
					/>
					{counter === 'daily' && (
						<>
							<div className="tptn-date-picker-container">
								<Text>{__('From', 'top-10')}</Text>
								<Button
									onClick={() =>
										setIsFromDatePickerVisible(
											(state) => !state
										)
									}
									variant="secondary"
								>
									{fromDate ||
										__('Select From Date', 'top-10')}
								</Button>
								{fromDate && (
									<Button
										onClick={() => clearDate(true)}
										variant="link"
										isDestructive
									>
										{__('Clear', 'top-10')}
									</Button>
								)}
								{isFromDatePickerVisible && (
									<Popover
										onClose={() =>
											setIsFromDatePickerVisible(false)
										}
									>
										<DatePicker
											currentDate={fromDate}
											onChange={(date) =>
												handleDateChange(date, true)
											}
										/>
									</Popover>
								)}
							</div>

							<div className="tptn-date-picker-container">
								<Text>{__('To', 'top-10')}</Text>
								<Button
									onClick={() =>
										setIsToDatePickerVisible(
											(state) => !state
										)
									}
									variant="secondary"
								>
									{toDate || __('Select To Date', 'top-10')}
								</Button>
								{toDate && (
									<Button
										onClick={() => clearDate(false)}
										variant="link"
										isDestructive
									>
										{__('Clear', 'top-10')}
									</Button>
								)}
								{isToDatePickerVisible && (
									<Popover
										onClose={() =>
											setIsToDatePickerVisible(false)
										}
									>
										<DatePicker
											currentDate={toDate}
											onChange={(date) =>
												handleDateChange(date, false)
											}
										/>
									</Popover>
								)}
							</div>
						</>
					)}
					<ToggleControl
						label={__('Number Formatting', 'top-10')}
						checked={numberFormat}
						onChange={(value) =>
							setAttributes({ numberFormat: value })
						}
					/>
				</PanelBody>

				<PanelBody
					title={__('Text Customization', 'top-10')}
					initialOpen={true}
				>
					<ToggleControl
						label={__('Advanced Mode', 'top-10')}
						checked={advancedMode}
						onChange={(value) =>
							setAttributes({ advancedMode: value })
						}
					/>

					{!advancedMode && (
						<>
							<TextControl
								label={__('Text Before Count', 'top-10')}
								value={textBefore}
								onChange={(value) =>
									setAttributes({ textBefore: value })
								}
							/>
							<TextControl
								label={__('Text After Count', 'top-10')}
								value={textAfter}
								onChange={(value) =>
									setAttributes({ textAfter: value })
								}
							/>
						</>
					)}

					{advancedMode && (
						<TextareaControl
							label={__('Advanced Text', 'top-10')}
							value={textAdvanced}
							onChange={(value) =>
								setAttributes({ textAdvanced: value })
							}
							help={__(
								'Use %totalcount% or %dailycount% as placeholders for the count value.',
								'top-10'
							)}
						/>
					)}
				</PanelBody>

				<PanelBody title={__('Icon', 'top-10')} initialOpen={true}>
					<div
						style={{
							display: 'flex',
							alignItems: 'center',
						}}
					>
						<TextControl
							label={__('Icon SVG HTML', 'top-10')}
							value={svgCode}
							onChange={(value) =>
								setAttributes({ svgCode: value })
							}
						/>
					</div>
					<div
						style={{
							display: 'flex',
							justifyContent: 'space-between',
							marginBottom: '10px',
						}}
					>
						<Button
							onClick={() => setIsSvgModalOpen(true)}
							variant="secondary"
						>
							{__('Select SVG', 'top-10')}
						</Button>
						<Button
							onClick={clearSvg}
							variant="secondary"
							isDestructive
						>
							{__('Clear', 'top-10')}
						</Button>
						{svgCode && (
							<div
								style={{
									marginRight: '10px',
									width: '30px',
									height: '30px',
								}}
								dangerouslySetInnerHTML={{ __html: svgCode }}
							/>
						)}
					</div>
					<SvgSelectionModal
						isOpen={isSvgModalOpen}
						onClose={() => setIsSvgModalOpen(false)}
						onSelect={handleSvgSelection}
					/>

					{svgCode && (
						<>
							<SelectControl
								label={__('Icon Location', 'top-10')}
								value={svgIconLocation}
								options={[
									{
										value: 'before',
										label: __('Before Text', 'top-10'),
									},
									{
										value: 'after',
										label: __('After Text', 'top-10'),
									},
								]}
								onChange={(newValue) =>
									setAttributes({ svgIconLocation: newValue })
								}
								help={
									svgIconLocation === 'after'
										? __(
												'Icon will be placed after the text.',
												'top-10'
											)
										: __(
												'Icon will be placed before the text.',
												'top-10'
											)
								}
							/>
							<UnitControl
								label={__('Icon Size', 'top-10')}
								value={svgIconSize}
								unit={svgIconSizeUnit}
								onValueChange={(value) =>
									setAttributes({ svgIconSize: value })
								}
								onUnitChange={(unit) =>
									setAttributes({ svgIconSizeUnit: unit })
								}
							/>
							<PaddingControl
								attributes={attributes}
								setAttributes={setAttributes}
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>
		</>
	);
};

export default Controls;

const SvgSelectionModal = ({ isOpen, onClose, onSelect }) => {
	return isOpen ? (
		<Modal title={__('Select an SVG', 'top-10')} onRequestClose={onClose}>
			<div
				style={{
					display: 'grid',
					gridTemplateColumns: 'repeat(auto-fill, minmax(25px, 1fr))',
					gap: '2px',
					padding: '1px',
				}}
			>
				{svgOptions.map((svg, index) => (
					<Button
						key={index}
						onClick={() => onSelect(svg.code)}
						style={{
							padding: '1px',
							border: '0px solid #ddd',
							display: 'flex',
							justifyContent: 'center',
							alignItems: 'center',
							height: '25px',
						}}
						title={svg.name}
					>
						<svg
							viewBox="0 0 100 100"
							xmlns="http://www.w3.org/2000/svg"
							style={{ width: '100%', height: '100%' }}
							dangerouslySetInnerHTML={{ __html: svg.code }}
						/>
					</Button>
				))}
			</div>
		</Modal>
	) : null;
};

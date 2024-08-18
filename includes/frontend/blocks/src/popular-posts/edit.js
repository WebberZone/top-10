import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Disabled, PanelBody } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import { ToggleControlGroup } from './components/toggle-control-group';
import { RangeControls } from './components/range-controls';
import { PostDisplayControls } from './components/post-display-controls';
import { StyleControls } from './components/style-controls';
import { OtherAttributesControl } from './components/other-attributes-control';

export default function Edit({ attributes, setAttributes }) {
	const {
		heading,
		daily,
		show_excerpt,
		show_author,
		show_date,
		disp_list_count,
		other_attributes,
	} = attributes;

	const blockProps = useBlockProps();

	const handleToggle = (attributeName) => () => {
		setAttributes({ [attributeName]: !attributes[attributeName] });
	};

	const handleChange = (attributeName) => (newValue) => {
		setAttributes({ [attributeName]: newValue });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Popular Posts Settings', 'top-10')}
					initialOpen={true}
				>
					<ToggleControlGroup
						controls={[
							{
								label: __('Custom period?', 'top-10'),
								attributeName: 'daily',
								checked: daily,
								onChange: handleToggle('daily'),
							},
						]}
					/>

					{daily && (
						<RangeControls
							attributes={attributes}
							onChange={handleChange}
						/>
					)}

					<PostDisplayControls
						attributes={attributes}
						onChange={handleChange}
					/>

					<ToggleControlGroup
						controls={[
							{
								label: __('Show heading', 'top-10'),
								attributeName: 'heading',
								checked: heading,
								onChange: handleToggle('heading'),
							},
							{
								label: __('Show excerpt', 'top-10'),
								attributeName: 'show_excerpt',
								checked: show_excerpt,
								onChange: handleToggle('show_excerpt'),
							},
							{
								label: __('Show author', 'top-10'),
								attributeName: 'show_author',
								checked: show_author,
								onChange: handleToggle('show_author'),
							},
							{
								label: __('Show date', 'top-10'),
								attributeName: 'show_date',
								checked: show_date,
								onChange: handleToggle('show_date'),
							},
							{
								label: __('Show count', 'top-10'),
								attributeName: 'disp_list_count',
								checked: disp_list_count,
								onChange: handleToggle('disp_list_count'),
							},
						]}
					/>

					<StyleControls
						attributes={attributes}
						onChange={handleChange}
					/>

					<OtherAttributesControl
						value={other_attributes}
						onChange={handleChange('other_attributes')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="top-10/popular-posts"
						attributes={attributes}
						urlQueryArgs={{ _locale: 'site' }}
					/>
				</Disabled>
			</div>
		</>
	);
}

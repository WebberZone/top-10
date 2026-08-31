/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';
import './style.scss';

import Controls from './controls';
import PostCountBlock from './post-count-block';

export default function Edit( { context, attributes, setAttributes } ) {
	const { textAlign } = attributes;

	const blockProps = useBlockProps( {
		className: clsx( 'wp-block-tptn-post-count', 'tptn-post-count', {
			[ `has-text-align-${ textAlign }` ]: textAlign,
			'tptn-advanced-mode': attributes.advancedMode,
		} ),
	} );

	return (
		<>
			<Controls
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<PostCountBlock
				attributes={ attributes }
				context={ context }
				blockProps={ blockProps }
			/>
		</>
	);
}

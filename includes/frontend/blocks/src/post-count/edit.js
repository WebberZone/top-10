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
import PostCountBlock from './post-count-block'; // Import the new component

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   root0              - The root object containing block properties.
 * @param {Object}   root0.context      - The context of the block.
 * @param {Object}   root0.attributes   - The attributes of the block.
 * @param {Function} root0.setAttributes - Function to set the block's attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit({ context, attributes, setAttributes }) {
	const { textAlign } = attributes;

	const blockProps = useBlockProps({
		className: clsx({
			[`has-text-align-${textAlign}`]: textAlign,
		}),
	});

	return (
		<>
			<Controls attributes={attributes} setAttributes={setAttributes} />
			<div {...blockProps}>
				<PostCountBlock attributes={attributes} context={context} />
			</div>
		</>
	);
}

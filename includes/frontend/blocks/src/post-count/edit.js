/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
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

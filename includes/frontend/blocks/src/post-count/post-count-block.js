import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import clsx from 'clsx';

const fetchCount = async (postId, counterType, blogId, fromDate, toDate) => {
	try {
		const response = await apiFetch({
			path: `/top-10/v1/counter/${postId}?counter=${counterType}&blog_id=${blogId}&from_date=${fromDate}&to_date=${toDate}`,
			method: 'GET',
		});
		return response;
	} catch (error) {
		console.error(`Error fetching ${counterType} count:`, error);
		return null;
	}
};

const fetchDefaultPostId = async () => {
	try {
		const response = await apiFetch({ path: '/wp/v2/posts?per_page=1' });
		return response.length > 0 ? response[0].id : null;
	} catch (error) {
		console.error('Error fetching default post ID:', error);
		return null;
	}
};

const PostCountBlock = ({ attributes, context }) => {
	const [counts, setCounts] = useState({
		total: null,
		daily: null,
		overall: null,
	});
	const [postId, setPostId] = useState(context?.postId || null);
	const {
		counter: counterType = 'total',
		blogId = 1,
		fromDate,
		toDate,
		textBefore = '',
		textAfter = '',
		textAdvanced = '',
		advancedMode = false,
		svgCode = '',
		svgIconSize = '1',
		svgIconSizeUnit = 'em',
		svgPaddingValues = [0, 0, 0, 0],
		svgPaddingUnits = ['px', 'px', 'px', 'px'],
		svgIconLocation = 'before',
		numberFormat = false,
		textAlign,
	} = attributes;

	const blockProps = useBlockProps({
		className: clsx({
			[`has-text-align-${textAlign}`]: textAlign,
		}),
	});

	useEffect(() => {
		const fetchCounts = async () => {
			if (!postId) {
				const defaultPostId = await fetchDefaultPostId();
				setPostId(defaultPostId);
				return;
			}

			const countTypes = ['total', 'daily', 'overall'];
			const fetchedCounts = {};

			for (const type of countTypes) {
				if (
					type === counterType ||
					textAdvanced.includes(`%${type}count%`)
				) {
					fetchedCounts[type] = await fetchCount(
						postId,
						type,
						blogId,
						fromDate,
						toDate
					);
				}
			}

			setCounts(fetchedCounts);
		};

		fetchCounts();
	}, [postId, counterType, blogId, fromDate, toDate, textAdvanced]);

	if (postId === null) {
		return (
			<div {...blockProps}>
				{__('No valid post ID found.', 'text-domain')}
			</div>
		);
	}

	if (Object.values(counts).every((count) => count === null)) {
		return <div {...blockProps}>Loading...</div>;
	}

	const formatNumber = (num) => {
		return numberFormat && num !== null && num !== undefined
			? num.toLocaleString()
			: num;
	};

	const renderContent = () => {
		if (!advancedMode || !textAdvanced) {
			return (
				<span className="tptn-post-count-text">
					{textBefore}
					{formatNumber(counts[counterType])}
					{textAfter}
				</span>
			);
		} else {
			const replacedText = textAdvanced.replace(
				/%(\w+)count%/g,
				(match, type) => formatNumber(counts[type] ?? 'N/A')
			);
			return <span className="tptn-post-count-text">{replacedText}</span>;
		}
	};
	const renderIcon = () => {
		if (!svgCode) {
			return null;
		}

		const paddingStyle = `padding:${svgPaddingValues.map((val, index) => `${val}${svgPaddingUnits[index]}`).join(' ')};`;
		const svgStyle = `width: ${svgIconSize}${svgIconSizeUnit}; height: ${svgIconSize}${svgIconSizeUnit}; ${paddingStyle}`;

		const svgWithStyle = svgCode.replace(
			'<svg',
			` <svg style="${svgStyle}"`
		);

		return (
			<span
				className="tptn-post-count-icon"
				dangerouslySetInnerHTML={{ __html: svgWithStyle }}
			/>
		);
	};

	const content = renderContent();
	const icon = renderIcon();

	return (
		<div
			{...blockProps}
			className={`wp-block-tptn-post-count tptn-post-count ${textAlign ? `has-text-align-${textAlign}` : ''} ${advancedMode ? 'tptn-advanced-mode' : ''}`}
		>
			{svgIconLocation === 'before' && icon}
			{content}
			{svgIconLocation === 'after' && icon}
		</div>
	);
};

export default PostCountBlock;

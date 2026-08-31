import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const countRequests = new Map();
let defaultPostIdRequest;

const fetchCount = ( postId, counterType, blogId, fromDate, toDate ) => {
	const query = { counter: counterType };

	if ( blogId ) {
		query.blog_id = blogId;
	}

	if ( fromDate ) {
		query.from_date = fromDate;
	}

	if ( toDate ) {
		query.to_date = toDate;
	}

	const path = addQueryArgs( `/top-10/v1/counter/${ postId }`, query );
	if ( countRequests.has( path ) ) {
		return countRequests.get( path );
	}

	const request = apiFetch( { path, method: 'GET' } ).catch( () => {
		// Do not retain failed requests in the shared cache. A transient REST
		// error must not poison the result for the editor session.
		countRequests.delete( path );
		return null;
	} );
	countRequests.set( path, request );

	return request;
};

const fetchDefaultPostId = () => {
	if ( ! defaultPostIdRequest ) {
		defaultPostIdRequest = apiFetch( {
			path: '/wp/v2/posts?per_page=1&_fields=id',
		} )
			.then( ( response ) =>
				response.length > 0 ? response[ 0 ].id : null
			)
			.catch( () => {
				// Allow a later block render to retry after a transient REST failure.
				defaultPostIdRequest = undefined;
				return null;
			} );
	}

	return defaultPostIdRequest;
};

const PostCountBlock = ( { attributes, context, blockProps } ) => {
	const [ counts, setCounts ] = useState( {
		total: null,
		daily: null,
		overall: null,
	} );
	const [ postId, setPostId ] = useState( context?.postId || null );
	const [ isResolvingPostId, setIsResolvingPostId ] = useState(
		! context?.postId
	);
	const requestId = useRef( 0 );
	const {
		counter: counterType = 'total',
		blogId = 0,
		fromDate,
		toDate,
		textBefore = '',
		textAfter = '',
		textAdvanced = '',
		advancedMode = false,
		svgCode = '',
		svgIconSize = '1',
		svgIconSizeUnit = 'em',
		svgPaddingValues = [ 0, 0, 0, 0 ],
		svgPaddingUnits = [ 'px', 'px', 'px', 'px' ],
		svgIconLocation = 'before',
		numberFormat = false,
	} = attributes;

	useEffect( () => {
		if ( context?.postId ) {
			setPostId( context.postId );
			setIsResolvingPostId( false );
			return;
		}

		let isCurrent = true;
		setIsResolvingPostId( true );

		fetchDefaultPostId().then( ( defaultPostId ) => {
			if ( ! isCurrent ) {
				return;
			}

			setPostId( defaultPostId );
			setIsResolvingPostId( false );
		} );

		return () => {
			isCurrent = false;
		};
	}, [ context?.postId ] );

	useEffect( () => {
		if ( ! postId ) {
			return undefined;
		}

		const currentRequestId = ++requestId.current;
		const countTypes =
			advancedMode && textAdvanced
				? [
						...new Set( [
							counterType,
							...[ 'total', 'daily', 'overall' ].filter(
								( type ) =>
									textAdvanced.includes( `%${ type }count%` )
							),
						] ),
				  ]
				: [ counterType ];

		setCounts( { total: null, daily: null, overall: null } );

		Promise.all(
			countTypes.map( ( type ) =>
				fetchCount( postId, type, blogId, fromDate, toDate ).then(
					( count ) => [ type, count ]
				)
			)
		).then( ( results ) => {
			if ( currentRequestId !== requestId.current ) {
				return;
			}

			setCounts( {
				total: null,
				daily: null,
				overall: null,
				...Object.fromEntries( results ),
			} );
		} );

		return () => {
			requestId.current += 1;
		};
	}, [
		postId,
		counterType,
		blogId,
		fromDate,
		toDate,
		textAdvanced,
		advancedMode,
	] );

	if ( isResolvingPostId ) {
		return <div { ...blockProps }>{ __( 'Loading…', 'top-10' ) }</div>;
	}

	if ( ! postId ) {
		return (
			<div { ...blockProps }>
				{ __( 'No valid post ID found.', 'top-10' ) }
			</div>
		);
	}

	if ( Object.values( counts ).every( ( count ) => count === null ) ) {
		return <div { ...blockProps }>{ __( 'Loading…', 'top-10' ) }</div>;
	}

	const formatNumber = ( num ) => {
		return numberFormat && num !== null && num !== undefined
			? num.toLocaleString()
			: num;
	};

	const renderContent = () => {
		if ( ! advancedMode || ! textAdvanced ) {
			return (
				<span className="tptn-post-count-text">
					{ textBefore }
					{ formatNumber( counts[ counterType ] ) }
					{ textAfter }
				</span>
			);
		}

		const replacedText = textAdvanced.replace(
			/%(\w+)count%/g,
			( match, type ) => formatNumber( counts[ type ] ?? 'N/A' )
		);
		return <span className="tptn-post-count-text">{ replacedText }</span>;
	};
	const renderIcon = () => {
		if ( ! svgCode ) {
			return null;
		}

		const paddingStyle = `padding:${ svgPaddingValues
			.map( ( val, index ) => `${ val }${ svgPaddingUnits[ index ] }` )
			.join( ' ' ) };`;
		const svgStyle = `width: ${ svgIconSize }${ svgIconSizeUnit }; height: ${ svgIconSize }${ svgIconSizeUnit }; ${ paddingStyle }`;

		const svgWithStyle = svgCode.replace(
			'<svg',
			` <svg style="${ svgStyle }"`
		);

		return (
			<span
				className="tptn-post-count-icon"
				dangerouslySetInnerHTML={ { __html: svgWithStyle } }
			/>
		);
	};

	const content = renderContent();
	const icon = renderIcon();

	return (
		<div { ...blockProps }>
			{ svgIconLocation === 'before' && icon }
			{ content }
			{ svgIconLocation === 'after' && icon }
		</div>
	);
};

export default PostCountBlock;

( function( blocks, i18n, element, components, editor, blockEditor ) {
	var el = element.createElement;
	const {registerBlockType} = blocks;
	const {__} = i18n; //translation functions
	var ServerSideRender = wp.serverSideRender;

	const { RichText, InspectorControls } = blockEditor;
	const {
		TextControl,
		CheckboxControl,
		RadioControl,
		SelectControl,
		TextareaControl,
		ToggleControl,
		RangeControl,
		Panel,
		PanelBody,
		PanelRow,
	} = components;

	registerBlockType( 'top-10/popular-posts', {
		title: __( 'Popular Posts [Top 10]', 'top-10' ),
		description: __( 'Display popular posts by Top 10', 'top-10' ),
		category: 'widgets',
		icon: 'editor-ol',
		keywords: [ __( 'popular posts' ), __( 'popular' ), __( 'posts' ) ],

		attributes: {
			heading: {
				type: 'boolean',
				default: false,
			},
			daily: {
				type: 'boolean',
				default: false,
			},
			daily_range: {
				type: 'number',
				default: 1,
			},
			hour_range: {
				type: 'number',
				default: 0,
			},
			limit: {
				type: 'number',
				default: 6,
			},
			offset: {
				type: 'number',
				default: 0,
			},
			show_excerpt: {
				type: 'boolean',
				default: false,
			},
			show_author: {
				type: 'boolean',
				default: false,
			},
			show_date: {
				type: 'boolean',
				default: false,
			},
			disp_list_count: {
				type: 'boolean',
				default: false,
			},
			post_thumb_op: {
				type: 'string',
				default: 'inline',
			},
			other_attributes: {
				type: 'string',
				default: '',
			},
		},

		supports: {
			html: false,
		},

		example: { },

		edit: function( props ) {
			const attributes =  props.attributes;
			const setAttributes =  props.setAttributes;

			if(props.isSelected){
	      	//	console.debug(props.attributes);
    		};


			// Functions to update attributes.
			function changeHeading(heading){
				setAttributes({heading});
			}

			function changeExcerpt(show_excerpt){
				setAttributes({show_excerpt});
			}

			function changeDaily(daily){
				setAttributes({daily});
			}

			function changeAuthor(show_author){
				setAttributes({show_author});
			}

			function changeDate(show_date){
				setAttributes({show_date});
			}

			function changeDisplayCount(disp_list_count){
				setAttributes({disp_list_count});
			}

			function changeThumbnail(post_thumb_op){
				setAttributes({post_thumb_op});
			}

			function changeOtherAttributes(other_attributes){
				setAttributes({other_attributes});
			}

			return [
				/**
				 * Server side render
				 */
				el("div", { className: props.className },
					el( ServerSideRender, {
					  block: 'top-10/popular-posts',
					  attributes: attributes
					} )
				),

				/**
				 * Inspector
				 */
				el( InspectorControls, {},
					el( PanelBody, { title: 'Related Posts Settings', initialOpen: true },

						el( ToggleControl, {
							label: __( 'Show heading', 'top-10' ),
							checked: attributes.heading,
							onChange: changeHeading
						} ),
						el( ToggleControl, {
							label: __( 'Custom period? Set range below', 'top-10' ),
							checked: attributes.daily,
							onChange: changeDaily
						} ),
						el( TextControl, {
							label: __( 'Daily range', 'top-10' ),
							value: attributes.daily_range,
							onChange: function( val ) {
								setAttributes( { daily_range: parseInt( val ) } );
							},
							type: 'number',
							min: 0,
							step: 1
						} ),
						el( TextControl, {
							label: __( 'Hourly range', 'top-10' ),
							value: attributes.hour_range,
							onChange: function( val ) {
								setAttributes( { hour_range: parseInt( val ) } );
							},
							type: 'number',
							min: 0,
							step: 1
						} ),
						el( TextControl, {
							label: __( 'No. of posts', 'top-10' ),
							value: attributes.limit,
							onChange: function( val ) {
								setAttributes( { limit: parseInt( val ) } );
							},
							type: 'number',
							min: 1,
							step: 1
						} ),

						el( TextControl, {
							label: __( 'Offset', 'top-10' ),
							value: attributes.offset,
							onChange: function( val ) {
								setAttributes( { offset: parseInt( val ) } );
							},
							type: 'number',
							min: 0,
							step: 1
						}),

						el( ToggleControl, {
							label: __( 'Show excerpt', 'top-10' ),
							checked: attributes.show_excerpt,
							onChange: changeExcerpt
						} ),
						el( ToggleControl, {
							label: __( 'Show author', 'top-10' ),
							checked: attributes.show_author,
							onChange: changeAuthor
						} ),
						el( ToggleControl, {
							label: __( 'Show date', 'top-10' ),
							checked: attributes.show_date,
							onChange: changeDate
						} ),
						el( ToggleControl, {
							label: __( 'Show count', 'top-10' ),
							checked: attributes.disp_list_count,
							onChange: changeDisplayCount
						} ),
						el(SelectControl, {
							value: attributes.post_thumb_op,
							label: __( 'Thumbnail options', 'top-10' ),
							onChange: changeThumbnail,
							options: [
								{value: 'inline', label: __( 'Before title', 'top-10' )},
								{value: 'after', label: __( 'After title', 'top-10' )},
								{value: 'thumbs_only', label: __( 'Only thumbnail', 'top-10' )},
								{value: 'text_only', label: __( 'Only text', 'top-10' )},
							]
						} ),
						el( TextareaControl, {
							label: __( 'Other attributes', 'top-10' ),
							help: __( 'Enter other attributes in a URL-style string-query. e.g. post_types=post,page&link_nofollow=1&exclude_post_ids=5,6', 'top-10' ),
							value: attributes.other_attributes,
							onChange: changeOtherAttributes
						} )
					),
				),
			]
		},

		save(){
			return null;//save has to exist. This all we need
		}
	} );
} )(
	window.wp.blocks,
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
	window.wp.editor,
	window.wp.blockEditor,
	window.wp.serverSideRender
);

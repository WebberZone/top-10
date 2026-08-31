<?php
// This file is generated. Do not modify it manually.
return array(
	'popular-posts' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'top-10/popular-posts',
		'version' => '2.0.0',
		'title' => 'Top 10 Popular Posts',
		'category' => 'widgets',
		'icon' => 'editor-ol',
		'keywords' => array(
			'top 10',
			'popular posts',
			'popular'
		),
		'description' => 'Display the Popular Posts',
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'className' => array(
				'type' => 'string'
			),
			'heading' => array(
				'type' => 'boolean'
			),
			'daily' => array(
				'type' => 'boolean'
			),
			'daily_range' => array(
				'type' => 'string'
			),
			'hour_range' => array(
				'type' => 'string'
			),
			'limit' => array(
				'type' => 'string'
			),
			'offset' => array(
				'type' => 'string'
			),
			'show_excerpt' => array(
				'type' => 'boolean'
			),
			'show_author' => array(
				'type' => 'boolean'
			),
			'show_date' => array(
				'type' => 'boolean'
			),
			'disp_list_count' => array(
				'type' => 'boolean'
			),
			'tptn_styles' => array(
				'type' => 'string'
			),
			'post_thumb_op' => array(
				'type' => 'string'
			),
			'other_attributes' => array(
				'type' => 'string'
			)
		),
		'textdomain' => 'top-10',
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js'
	),
	'post-count' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'top-10/post-count',
		'version' => '1.0.0',
		'title' => 'Top 10 Post Count',
		'category' => 'text',
		'description' => 'Display the number of visits for a post.',
		'attributes' => array(
			'className' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string'
			),
			'counter' => array(
				'type' => 'string',
				'default' => 'total'
			),
			'fromDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'toDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'textBefore' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAfter' => array(
				'type' => 'string',
				'default' => ''
			),
			'advancedMode' => array(
				'type' => 'boolean',
				'default' => false
			),
			'textAdvanced' => array(
				'type' => 'string',
				'default' => ''
			),
			'numberFormat' => array(
				'type' => 'boolean',
				'default' => false
			),
			'svgCode' => array(
				'type' => 'string',
				'default' => ''
			),
			'svgIconLocation' => array(
				'type' => 'string',
				'default' => 'before'
			),
			'svgPaddingValues' => array(
				'type' => 'array',
				'default' => array(
					0,
					0,
					0,
					0
				),
				'items' => array(
					'type' => 'number'
				)
			),
			'svgPaddingUnits' => array(
				'type' => 'array',
				'default' => array(
					'px',
					'px',
					'px',
					'px'
				),
				'items' => array(
					'type' => 'string'
				)
			),
			'svgIconSize' => array(
				'type' => 'string',
				'default' => '1'
			),
			'svgIconSizeUnit' => array(
				'type' => 'string',
				'default' => 'em'
			)
		),
		'usesContext' => array(
			'postId',
			'postType',
			'queryId'
		),
		'example' => array(
			'viewportWidth' => 350
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full'
			),
			'html' => false,
			'color' => array(
				'gradients' => true,
				'link' => true,
				'__experimentalDefaultControls' => array(
					'background' => true,
					'text' => true,
					'link' => true
				)
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalFontStyle' => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => array(
					'fontSize' => true
				)
			),
			'interactivity' => array(
				'clientNavigation' => true
			)
		),
		'textdomain' => 'top-10',
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'viewStyle' => 'file:./style-index.css'
	)
);

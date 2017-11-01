/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    config.filebrowserImageBrowseUrl = '/plugins/ckfinder/3.4.1/ckfinder.html?type=Images';
    config.allowedContent = true;
    config.extraPlugins = 'uploadimage,image2,html5video,copyformatting';
    config.height = 800;
    config.contentsCss = [CKEDITOR.basePath + 'contents.css', '/css/admin/template.css'];
    config.image2_alignClasses = ['image-align-left', 'image-align-center', 'image-align-right'];
    config.image2_disableResizer = true;
    config.font_names='宋体/宋体;黑体/黑体;仿宋/仿宋_GB2312;楷体/楷体_GB2312;隶书/隶书;幼圆/幼圆;微软雅黑/微软雅黑;'+ config.font_names;
};

(()=>{"use strict";var e,t={276:(e,t,o)=>{const n=window.wp.element,l=window.wp.primitives,r=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"M15.5 9.5a1 1 0 100-2 1 1 0 000 2zm0 1.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-2.25 6v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM9.5 8.5a1 1 0 11-2 0 1 1 0 012 0zm1.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z",fillRule:"evenodd"})),a=window.wp.blocks,s=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"dss/jobbnorge","version":"1.0.8","title":"Jobbnorge","category":"widgets","icon":"people","description":"Viser jobber fra jobbnorge.no","keywords":["jobb","jobbnorge","jobbnorge.no"],"supports":{"html":false},"attributes":{"columns":{"type":"number","default":3},"blockLayout":{"type":"string","default":"grid"},"feedURL":{"type":"string","default":""},"noJobsMessage":{"type":"string","default":""},"itemsToShow":{"type":"number","default":5},"displayExcerpt":{"type":"boolean","default":true},"displayDeadline":{"type":"boolean","default":false},"displayScope":{"type":"boolean","default":false},"displayDuration":{"type":"boolean","default":false},"displayDate":{"type":"boolean","default":true},"excerptLength":{"type":"number","default":55}},"textdomain":"wp-jobbnorge-block","editorScript":"file:init.js","editorStyle":"file:editor.scss","style":"file:style.scss"}'),i=window.wp.blockEditor,c=window.wp.components,d=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"M20.1 5.1L16.9 2 6.2 12.7l-1.3 4.4 4.5-1.3L20.1 5.1zM4 20.8h8v-1.5H4v1.5z"})),p=(0,n.createElement)(l.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,n.createElement)(l.Path,{d:"M4 4v1.5h16V4H4zm8 8.5h8V11h-8v1.5zM4 20h16v-1.5H4V20zm4-8c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z"})),b=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7.8 16.5H5c-.3 0-.5-.2-.5-.5v-6.2h6.8v6.7zm0-8.3H4.5V5c0-.3.2-.5.5-.5h6.2v6.7zm8.3 7.8c0 .3-.2.5-.5.5h-6.2v-6.8h6.8V19zm0-7.8h-6.8V4.5H19c.3 0 .5.2.5.5v6.2z",fillRule:"evenodd",clipRule:"evenodd"})),m=window.wp.i18n,u=window.wp.url,g=window.wp.serverSideRender;var w=o.n(g);const{name:h}=s;(e=>{const{metadata:t,settings:o,name:n}=e;(0,a.registerBlockType)({name:n,...t},o)})({name:h,metadata:s,settings:{icon:r,example:{attributes:{feedURL:"https://wordpress.org"}},edit:function(e){let{attributes:t,setAttributes:o}=e;const[l,a]=(0,n.useState)(!t.feedURL),{blockLayout:s,columns:g,displayScope:h,displayDuration:v,displayDate:f,displayExcerpt:_,excerptLength:y,feedURL:k,itemsToShow:E,noJobsMessage:x}=t;function j(e){return()=>{const n=t[e];o({[e]:!n})}}const C=(0,i.useBlockProps)();if(l)return(0,n.createElement)("div",C,(0,n.createElement)(c.Placeholder,{icon:r,label:"Jobbnorge"},(0,n.createElement)("form",{onSubmit:function(e){e.preventDefault(),k&&(o({feedURL:(0,u.prependHTTP)(k)}),a(!1))},className:"wp-block-dss-jobbnorge__placeholder-form"},(0,n.createElement)(c.TextControl,{placeholder:(0,m.__)("Enter URL here…"),value:k,onChange:e=>o({feedURL:e}),className:"wp-block-dss-jobbnorge__placeholder-input"}),(0,n.createElement)(c.Button,{variant:"primary",type:"submit"},(0,m.__)("Use URL")))));const L=[{icon:d,title:(0,m.__)("Edit Jobbnorge URL","wp-jobbnorge-block"),onClick:()=>a(!0)},{icon:p,title:(0,m.__)("List view"),onClick:()=>o({blockLayout:"list"}),isActive:"list"===s},{icon:b,title:(0,m.__)("Grid view"),onClick:()=>o({blockLayout:"grid"}),isActive:"grid"===s}];return(0,n.createElement)(n.Fragment,null,(0,n.createElement)(i.BlockControls,null,(0,n.createElement)(c.ToolbarGroup,{controls:L})),(0,n.createElement)(i.InspectorControls,null,(0,n.createElement)(c.PanelBody,{title:(0,m.__)("Settings")},(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,m.__)("Number of items"),value:E,onChange:e=>o({itemsToShow:e}),min:1,max:20,required:!0}),_&&(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,m.__)("Max number of words in excerpt"),value:y,onChange:e=>o({excerptLength:e}),min:10,max:100,required:!0}),(0,n.createElement)(c.TextareaControl,{label:(0,m.__)("No jobs found message","wp-jobbnorge-block"),help:(0,m.__)("Message to display if no jobs are found","wp-jobbnorge-block"),value:x||(0,m.__)("There are no jobs at this time.","wp-jobbnorge-block"),onChange:e=>o({noJobsMessage:e})})),(0,n.createElement)(c.PanelBody,{title:(0,m.__)("Item","wp-jobbnorge-block")},(0,n.createElement)(c.ToggleControl,{label:(0,m.__)("Display excerpt"),checked:_,onChange:j("displayExcerpt")}),(0,n.createElement)(c.ToggleControl,{label:(0,m.__)("Display deadline","wp-jobbnorge-block"),checked:f,onChange:j("displayDate")}),(0,n.createElement)(c.ToggleControl,{label:(0,m.__)("Display scope","wp-jobbnorge-block"),checked:h,onChange:j("displayScope")}),(0,n.createElement)(c.ToggleControl,{label:(0,m.__)("Display duration","wp-jobbnorge-block"),checked:v,onChange:j("displayDuration")})),"grid"===s&&(0,n.createElement)(c.PanelBody,{title:(0,m.__)("Grid view")},(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,m.__)("Columns"),value:g,onChange:e=>o({columns:e}),min:2,max:6,required:!0}))),(0,n.createElement)("div",C,(0,n.createElement)(c.Disabled,null,(0,n.createElement)(w(),{block:"dss/jobbnorge",attributes:t,httpMethod:"POST"}))))}}})}},o={};function n(e){var l=o[e];if(void 0!==l)return l.exports;var r=o[e]={exports:{}};return t[e](r,r.exports,n),r.exports}n.m=t,e=[],n.O=(t,o,l,r)=>{if(!o){var a=1/0;for(d=0;d<e.length;d++){o=e[d][0],l=e[d][1],r=e[d][2];for(var s=!0,i=0;i<o.length;i++)(!1&r||a>=r)&&Object.keys(n.O).every((e=>n.O[e](o[i])))?o.splice(i--,1):(s=!1,r<a&&(a=r));if(s){e.splice(d--,1);var c=l();void 0!==c&&(t=c)}}return t}r=r||0;for(var d=e.length;d>0&&e[d-1][2]>r;d--)e[d]=e[d-1];e[d]=[o,l,r]},n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var o in t)n.o(t,o)&&!n.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={410:0,308:0};n.O.j=t=>0===e[t];var t=(t,o)=>{var l,r,a=o[0],s=o[1],i=o[2],c=0;if(a.some((t=>0!==e[t]))){for(l in s)n.o(s,l)&&(n.m[l]=s[l]);if(i)var d=i(n)}for(t&&t(o);c<a.length;c++)r=a[c],n.o(e,r)&&e[r]&&e[r][0](),e[r]=0;return n.O(d)},o=self.webpackChunkwp_jobbnorge_block=self.webpackChunkwp_jobbnorge_block||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))})();var l=n.O(void 0,[308],(()=>n(276)));l=n.O(l)})();
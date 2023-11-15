!function(){"use strict";var e,o={622:function(e,o,t){var n=window.React,l=window.wp.primitives,r=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"M15.5 9.5a1 1 0 100-2 1 1 0 000 2zm0 1.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-2.25 6v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM9.5 8.5a1 1 0 11-2 0 1 1 0 012 0zm1.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z",fillRule:"evenodd"})),a=window.wp.blocks,i=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"dss/jobbnorge","version":"2.0.0","title":"Jobbnorge","category":"widgets","icon":"people","description":"List jobs at jobbnorge.no","keywords":["jobbnorge","jobbnorge.no"],"supports":{"html":false},"attributes":{"columns":{"type":"number","default":3},"blockLayout":{"type":"string","default":"grid"},"employerID":{"type":"string","default":""},"noJobsMessage":{"type":"string","default":""},"itemsToShow":{"type":"number","default":5},"displayEmployer":{"type":"boolean","default":false},"displayExcerpt":{"type":"boolean","default":true},"displayDeadline":{"type":"boolean","default":false},"displayScope":{"type":"boolean","default":false},"displayDuration":{"type":"boolean","default":false},"displayDate":{"type":"boolean","default":true},"excerptLength":{"type":"number","default":55}},"textdomain":"wp-jobbnorge-block","editorScript":"file:init.js","editorStyle":"file:editor.scss","style":"file:style.scss"}'),s=window.wp.blockEditor,c=window.wp.components,b=window.wp.element,p=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"m19 7-3-3-8.5 8.5-1 4 4-1L19 7Zm-7 11.5H5V20h7v-1.5Z"})),d=(0,n.createElement)(l.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,n.createElement)(l.Path,{d:"M4 4v1.5h16V4H4zm8 8.5h8V11h-8v1.5zM4 20h16v-1.5H4V20zm4-8c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z"})),m=(0,n.createElement)(l.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(l.Path,{d:"m3 5c0-1.10457.89543-2 2-2h13.5c1.1046 0 2 .89543 2 2v13.5c0 1.1046-.8954 2-2 2h-13.5c-1.10457 0-2-.8954-2-2zm2-.5h6v6.5h-6.5v-6c0-.27614.22386-.5.5-.5zm-.5 8v6c0 .2761.22386.5.5.5h6v-6.5zm8 0v6.5h6c.2761 0 .5-.2239.5-.5v-6zm0-8v6.5h6.5v-6c0-.27614-.2239-.5-.5-.5z",fillRule:"evenodd",clipRule:"evenodd"})),u=window.wp.i18n,g=window.wp.serverSideRender,w=t.n(g),v=window.wp.data;const{name:h}=i;(e=>{const{metadata:o,settings:t,name:n}=e;(0,a.registerBlockType)({name:n,...o},t)})({name:h,metadata:i,settings:{icon:r,example:{attributes:{employerID:"123[, 456, 789]"}},edit:function({attributes:e,setAttributes:o}){const[t,l]=(0,b.useState)(!e.employerID),{blockLayout:a,columns:i,displayScope:g,displayDuration:h,displayDate:y,displayEmployer:f,displayExcerpt:_,excerptLength:k,employerID:E,itemsToShow:j,noJobsMessage:x}=e;function C(t){return()=>{const n=e[t];o({[t]:!n})}}(0,v.dispatch)("core").addEntities([{name:"jobbnorge/employers",kind:"dss/v1",baseURL:"/dss/v1/jobbnorge/employers",key:"value"}]);const D=(0,v.select)("core").getEntityRecords("dss/v1","jobbnorge/employers",{per_page:100});console.log(D);const S=(0,s.useBlockProps)();if(t)return(0,n.createElement)("div",{...S},(0,n.createElement)(c.Placeholder,{icon:r,label:"Jobbnorge"},(0,n.createElement)("form",{onSubmit:function(e){e.preventDefault(),E&&(o({employerID:E}),l(!1))},className:"wp-block-dss-jobbnorge__placeholder-form"},D?(0,n.createElement)(c.SelectControl,{multiple:!0,options:(null!=D?D:[]).map((e=>{var o;return{label:e.label,value:e.value,disabled:null!==(o=e?.disabled)&&void 0!==o&&o}})),className:"wp-block-dss-jobbnorge__placeholder-input",help:(0,u.__)("Select employers to display. Ctrl + Click to select more than one","wp-jobbnorge-block"),__nextHasNoMarginBottom:!0}):(0,n.createElement)(c.TextControl,{placeholder:(0,u.__)("Employer ID [,id2, id3, ..]","wp-jobbnorge-block"),value:E,onChange:e=>o({employerID:e}),className:"wp-block-dss-jobbnorge__placeholder-input"}),(0,n.createElement)(c.Button,{variant:"primary",type:"submit"},(0,u.__)("Save","wp-jobbnorge-block")))));const z=[{icon:p,title:(0,u.__)("Edit Jobbnorge URL","wp-jobbnorge-block"),onClick:()=>l(!0)},{icon:d,title:(0,u.__)("List view","wp-jobbnorge-block"),onClick:()=>o({blockLayout:"list"}),isActive:"list"===a},{icon:m,title:(0,u.__)("Grid view","wp-jobbnorge-block"),onClick:()=>o({blockLayout:"grid"}),isActive:"grid"===a}];return(0,n.createElement)(n.Fragment,null,(0,n.createElement)(s.BlockControls,null,(0,n.createElement)(c.ToolbarGroup,{controls:z})),(0,n.createElement)(s.InspectorControls,null,(0,n.createElement)(c.PanelBody,{title:(0,u.__)("Settings","wp-jobbnorge-block")},(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,u.__)("Number of items","wp-jobbnorge-block"),value:j,onChange:e=>o({itemsToShow:e}),min:1,max:20,required:!0}),_&&(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,u.__)("Max number of words in excerpt","wp-jobbnorge-block"),value:k,onChange:e=>o({excerptLength:e}),min:10,max:100,required:!0}),(0,n.createElement)(c.TextareaControl,{label:(0,u.__)("No jobs found message","wp-jobbnorge-block"),help:(0,u.__)("Message to display if no jobs are found","wp-jobbnorge-block"),value:x||(0,u.__)("There are no jobs at this time.","wp-jobbnorge-block"),onChange:e=>o({noJobsMessage:e})})),(0,n.createElement)(c.PanelBody,{title:(0,u.__)("Item","wp-jobbnorge-block")},(0,n.createElement)(c.ToggleControl,{label:(0,u.__)("Display employer","wp-jobbnorge-block"),checked:f,onChange:C("displayEmployer")}),(0,n.createElement)(c.ToggleControl,{label:(0,u.__)("Display excerpt","wp-jobbnorge-block"),checked:_,onChange:C("displayExcerpt")}),(0,n.createElement)(c.ToggleControl,{label:(0,u.__)("Display deadline","wp-jobbnorge-block"),checked:y,onChange:C("displayDate")}),(0,n.createElement)(c.ToggleControl,{label:(0,u.__)("Display scope","wp-jobbnorge-block"),checked:g,onChange:C("displayScope")}),(0,n.createElement)(c.ToggleControl,{label:(0,u.__)("Display duration","wp-jobbnorge-block"),checked:h,onChange:C("displayDuration")})),"grid"===a&&(0,n.createElement)(c.PanelBody,{title:(0,u.__)("Grid view","wp-jobbnorge-block")},(0,n.createElement)(c.RangeControl,{__nextHasNoMarginBottom:!0,label:(0,u.__)("Columns","wp-jobbnorge-block"),value:i,onChange:e=>o({columns:e}),min:2,max:6,required:!0}))),(0,n.createElement)("div",{...S},(0,n.createElement)(c.Disabled,null,(0,n.createElement)(w(),{block:"dss/jobbnorge",attributes:e,httpMethod:"POST"}))))}}})}},t={};function n(e){var l=t[e];if(void 0!==l)return l.exports;var r=t[e]={exports:{}};return o[e](r,r.exports,n),r.exports}n.m=o,e=[],n.O=function(o,t,l,r){if(!t){var a=1/0;for(b=0;b<e.length;b++){t=e[b][0],l=e[b][1],r=e[b][2];for(var i=!0,s=0;s<t.length;s++)(!1&r||a>=r)&&Object.keys(n.O).every((function(e){return n.O[e](t[s])}))?t.splice(s--,1):(i=!1,r<a&&(a=r));if(i){e.splice(b--,1);var c=l();void 0!==c&&(o=c)}}return o}r=r||0;for(var b=e.length;b>0&&e[b-1][2]>r;b--)e[b]=e[b-1];e[b]=[t,l,r]},n.n=function(e){var o=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(o,{a:o}),o},n.d=function(e,o){for(var t in o)n.o(o,t)&&!n.o(e,t)&&Object.defineProperty(e,t,{enumerable:!0,get:o[t]})},n.o=function(e,o){return Object.prototype.hasOwnProperty.call(e,o)},function(){var e={410:0,308:0};n.O.j=function(o){return 0===e[o]};var o=function(o,t){var l,r,a=t[0],i=t[1],s=t[2],c=0;if(a.some((function(o){return 0!==e[o]}))){for(l in i)n.o(i,l)&&(n.m[l]=i[l]);if(s)var b=s(n)}for(o&&o(t);c<a.length;c++)r=a[c],n.o(e,r)&&e[r]&&e[r][0](),e[r]=0;return n.O(b)},t=self.webpackChunkwp_jobbnorge_block=self.webpackChunkwp_jobbnorge_block||[];t.forEach(o.bind(null,0)),t.push=o.bind(null,t.push.bind(t))}();var l=n.O(void 0,[308],(function(){return n(622)}));l=n.O(l)}();
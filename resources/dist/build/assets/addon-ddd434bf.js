function m(t,e,i,n,l,o,_,h){var s=typeof t=="function"?t.options:t;e&&(s.render=e,s.staticRenderFns=i,s._compiled=!0),n&&(s.functional=!0),o&&(s._scopeId="data-v-"+o);var r;if(_?(r=function(a){a=a||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,!a&&typeof __VUE_SSR_CONTEXT__<"u"&&(a=__VUE_SSR_CONTEXT__),l&&l.call(this,a),a&&a._registeredComponents&&a._registeredComponents.add(_)},s._ssrRegister=r):l&&(r=h?function(){l.call(this,(s.functional?this.parent:this).$root.$options.shadowRoot)}:l),r)if(s.functional){s._injectStyles=r;var p=s.render;s.render=function(v,d){return r.call(d),p(v,d)}}else{var c=s.beforeCreate;s.beforeCreate=c?[].concat(c,r):[r]}return{exports:t,options:s}}const f={mixins:[Fieldtype],data(){return{showingPreview:!1}},computed:{allowGenerate(){var t;return(t=this.config)==null?void 0:t.generate_on_upload},showPreview(){var t;return(t=this.config)==null?void 0:t.preview_placeholder},isAsset(){var t;return((t=this.meta)==null?void 0:t.is_asset)||!1},isSupported(){var t;return((t=this.meta)==null?void 0:t.is_supported)||!1},uri(){var t;return(t=this.meta)==null?void 0:t.uri},hash(){var t;return(t=this.meta)==null?void 0:t.hash},provider(){var t;return(t=this.meta)==null?void 0:t.provider}}};var u=function(){var e=this,i=e._self._c;return i("div",[!e.isAsset||!e.isSupported?i("div",{staticClass:"help-block flex items-center mb-3"},[i("svg-icon",{staticClass:"h-4",attrs:{name:"hidden"}}),i("span",{staticClass:"ml-2"},[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.not_generated"))+": "),e.isSupported?[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.no_asset"))+" ")]:[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.not_supported"))+" ")]],2)],1):e.uri?i("div",[e.showPreview?e._e():i("div",{staticClass:"help-block flex items-center"},[i("svg-icon",{staticClass:"h-4",attrs:{name:"synchronize"}}),i("span",{staticClass:"ml-2",attrs:{title:this.value.id}},[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.generated"))+" ")])],1),e.showPreview?i("div",{staticClass:"mt-3"},[i("div",{staticClass:"flex"},[i("img",{staticClass:"btn btn-flat btn-sm p-0 overflow-hidden w-auto",class:{"opacity-25":e.showingPreview},attrs:{src:e.uri}}),i("button",{staticClass:"btn btn-flat btn-sm ml-2",attrs:{type:"button"},on:{click:function(n){e.showingPreview=!e.showingPreview}}},[e.showingPreview?[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.hide_preview"))+" ")]:[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.show_preview"))+" ")]],2)]),i("div",{staticClass:"btn-group"},[i("button",{staticClass:"btn p-0 overflow-hidden",attrs:{type:"button"},on:{click:function(n){e.showingPreview=!e.showingPreview}}},[i("img",{staticClass:"h-9 w-auto",class:{"opacity-25":e.showingPreview},attrs:{src:e.uri}})]),i("button",{staticClass:"btn",attrs:{type:"button"},on:{click:function(n){e.showingPreview=!e.showingPreview}}},[e.showingPreview?[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.hide_preview"))+" ")]:[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.show_preview"))+" ")]],2)]),e.showingPreview?i("img",{staticClass:"btn p-0 h-8 min-h-40 w-auto rounded-md mt-3",attrs:{src:e.uri},on:{click:function(n){e.showingPreview=!e.showingPreview}}}):e._e()]):e._e()]):i("div",[i("div",{staticClass:"help-block flex items-center mb-3"},[i("svg-icon",{staticClass:"h-4",attrs:{name:"close"}}),i("span",{staticClass:"ml-2"},[e._v(" "+e._s(e.__("statamic-placeholder-images::fieldtypes.placeholder_image.field.not_yet_generated"))+" ")])],1)])])},g=[],w=m(f,u,g,!1,null,null,null,null);const C=w.exports;Statamic.$components.register("placeholder_image-fieldtype",C);

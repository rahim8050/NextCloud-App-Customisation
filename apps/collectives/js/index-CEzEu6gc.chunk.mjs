/*! third party licenses: js/vendor.LICENSE.txt */
function r(e,i,a){const t=`#initial-state-${e}-${i}`;if(window._nc_initial_state?.has(t))return window._nc_initial_state.get(t);window._nc_initial_state||(window._nc_initial_state=new Map);const o=document.querySelector(t);if(o===null)return a;try{const n=JSON.parse(atob(o.value));return window._nc_initial_state.set(t,n),n}catch(n){return console.error("[@nextcloud/initial-state] Could not parse initial state",{key:i,app:e,error:n}),a}}export{r as l};
//# sourceMappingURL=index-CEzEu6gc.chunk.mjs.map

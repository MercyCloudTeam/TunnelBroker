import{d as y,u as B,o as n,c as w,w as r,g as i,a as l,b as a,v as m,E as f,h as o,e as c,i as p,t as u,F as _,n as h}from"./app-29e4dbf0.js";import{_ as A}from"./ActionMessage-e48ea84c.js";import{_ as V}from"./FormSection-704b3603.js";import{_ as g}from"./InputError-9857b6e5.js";import{_ as v}from"./InputLabel-45914348.js";import{_ as $}from"./PrimaryButton-5b8e2c49.js";import"./SectionTitle-8710607c.js";import"./_plugin-vue_export-helper-c27b6911.js";const x={class:"col-span-6 sm:col-span-4"},C=["value"],N={class:"col-span-6 sm:col-span-4"},k=["value"],L={__name:"Add",props:{asn:Array,tunnels:Array},setup(d){const S=y(null),s=B({asn:"",tunnel:""}),b=()=>{s.post(route("bgp.store"),{errorBag:"CreateBGP",preserveScroll:!0,onSuccess:()=>s.reset(),onError:()=>{s.errors.asn&&(s.reset(),S.value.focus())}})};return(E,t)=>(n(),w(V,{onSubmitted:b},{title:r(()=>[i(" Add BGP Session ")]),description:r(()=>[]),form:r(()=>[l("div",x,[a(v,{for:"asn",value:"ASN"}),m(l("select",{class:"border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":t[0]||(t[0]=e=>o(s).asn=e)},[(n(!0),c(_,null,p(d.asn,e=>(n(),c("option",{value:e.id}," AS"+u(e.asn),9,C))),256))],512),[[f,o(s).asn]]),a(g,{message:o(s).errors.asn,class:"mt-2"},null,8,["message"])]),l("div",N,[a(v,{for:"tunnel",value:"Tunnel"}),m(l("select",{class:"border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm","onUpdate:modelValue":t[1]||(t[1]=e=>o(s).tunnel=e)},[(n(!0),c(_,null,p(d.tunnels,e=>(n(),c("option",{value:e.tunnel_id}," #"+u(e.tunnel_id)+" "+u(e.remote),9,k))),256))],512),[[f,o(s).tunnel]]),a(g,{message:o(s).errors.tunnel,class:"mt-2"},null,8,["message"])])]),actions:r(()=>[a(A,{on:o(s).recentlySuccessful,class:"mr-3"},{default:r(()=>[i(" Success. ")]),_:1},8,["on"]),a($,{class:h({"opacity-25":o(s).processing}),disabled:o(s).processing},{default:r(()=>[i(" Create ")]),_:1},8,["class","disabled"])]),_:1}))}};export{L as default};

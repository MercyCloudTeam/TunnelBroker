import{u as d,d as m,o as c,e as u,b as e,h as o,w as r,F as p,H as f,a,n as _,g as w,j as b}from"./app-07771b8c.js";import{A as h}from"./AuthenticationCard-b8c3b92b.js";import{_ as x}from"./InputError-d19d736c.js";import{_ as g}from"./InputLabel-65e49a45.js";import{_ as k}from"./PrimaryButton-d0479d09.js";import{_ as y}from"./TextInput-99762df9.js";import"./_plugin-vue_export-helper-c27b6911.js";const v=a("p",{class:"text-xl font-bold text-black underline decoration-sky-500"},"TunnelBroker.IO",-1),V=a("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1),C=["onSubmit"],B={class:"flex justify-end mt-4"},H={__name:"ConfirmPassword",setup(F){const s=d({password:""}),t=m(null),i=()=>{s.post(route("password.confirm"),{onFinish:()=>{s.reset(),t.value.focus()}})};return($,n)=>(c(),u(p,null,[e(o(f),{title:"Secure Area"}),e(h,null,{logo:r(()=>[v]),default:r(()=>[V,a("form",{onSubmit:b(i,["prevent"])},[a("div",null,[e(g,{for:"password",value:"Password"}),e(y,{id:"password",ref_key:"passwordInput",ref:t,modelValue:o(s).password,"onUpdate:modelValue":n[0]||(n[0]=l=>o(s).password=l),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),e(x,{class:"mt-2",message:o(s).errors.password},null,8,["message"])]),a("div",B,[e(k,{class:_(["ml-4",{"opacity-25":o(s).processing}]),disabled:o(s).processing},{default:r(()=>[w(" Confirm ")]),_:1},8,["class","disabled"])])],40,C)]),_:1})],64))}};export{H as default};
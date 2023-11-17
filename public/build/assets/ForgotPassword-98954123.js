import{u as c,o as i,e as m,b as e,h as t,w as a,F as u,H as _,t as f,f as p,a as o,n as w,g as x,j as b}from"./app-07771b8c.js";import{A as g}from"./AuthenticationCard-b8c3b92b.js";import{_ as h}from"./InputError-d19d736c.js";import{_ as k}from"./InputLabel-65e49a45.js";import{_ as y}from"./PrimaryButton-d0479d09.js";import{_ as F}from"./TextInput-99762df9.js";import V from"./CardFooter-ec978700.js";import"./_plugin-vue_export-helper-c27b6911.js";const v=o("p",{class:"text-xl font-bold text-black underline decoration-sky-500"},"TunnelBroker.IO",-1),N=o("p",{class:"text-center text-2xl mb-4 mt-4 font-bold text-black"},"Forgot Password",-1),$=o("div",{class:"mb-4 text-sm text-gray-600"}," Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one. ",-1),B={key:0,class:"mb-4 font-medium text-sm text-green-600"},C=["onSubmit"],P={class:"flex items-center justify-end mt-4"},I={__name:"ForgotPassword",props:{status:String},setup(l){const s=c({email:""}),n=()=>{s.post(route("password.email"))};return(S,r)=>(i(),m(u,null,[e(t(_),{title:"Forgot Password"}),e(g,null,{logo:a(()=>[v]),default:a(()=>[N,$,l.status?(i(),m("div",B,f(l.status),1)):p("",!0),o("form",{onSubmit:b(n,["prevent"])},[o("div",null,[e(k,{for:"email",value:"Email"}),e(F,{id:"email",modelValue:t(s).email,"onUpdate:modelValue":r[0]||(r[0]=d=>t(s).email=d),type:"email",class:"mt-1 block w-full",required:"",autofocus:""},null,8,["modelValue"]),e(h,{class:"mt-2",message:t(s).errors.email},null,8,["message"])]),o("div",P,[e(y,{class:w({"opacity-25":t(s).processing}),disabled:t(s).processing},{default:a(()=>[x(" Email Password Reset Link ")]),_:1},8,["class","disabled"])])],40,C),e(V)]),_:1})],64))}};export{I as default};
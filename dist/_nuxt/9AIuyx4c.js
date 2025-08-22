import{r,e as F,f as H,g as w,h as S,c as u,a,i as T,j as f,v as k,F as V,k as J,l as Y,m as I,t as b,n as C,o as c,p as E}from"./Btc6xiXW.js";import{S as p}from"./9Qm2jZ7z.js";import{_ as Q}from"./DlAUqK2U.js";const R={class:"flex items-center justify-between pt-2"},W={class:"flex-1"},X={class:"datatable w-full rounded-md overflow-hidden"},Z={class:"px-4 py-2"},ee={class:"px-4 py-2"},ae={class:"px-4 py-2"},te={class:"px-4 py-2"},se={class:"px-4 py-2"},ne={class:"flex space-x-2"},oe=["onClick"],le=["onClick"],re={class:"flex justify-between items-center mt-4 text-xs"},ie={class:"flex items-center space-x-2"},de={class:"flex items-center space-x-2"},ue=["disabled"],ce=["onClick","disabled"],be=["disabled"],pe={key:0,class:"fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50"},ge={class:"bg-white p-6 rounded-lg shadow-lg max-w-lg w-full"},me={class:"mb-4"},ve={class:"mb-4"},he={class:"flex justify-end"},xe=["disabled"],fe={key:1,class:"fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 z-50"},_e={class:"bg-white p-6 rounded-lg shadow-lg max-w-md w-full"},je={class:"mb-4"},ye={__name:"code",setup(we){const g=r(""),i=r([]),d=r({jenisbarang_nama:"",jenisbarang_kode:"",jenisbarang_jumlah:0}),B=r(!1),_=r(!1),j=r(null),m=r(1),v=r(""),o=r(1),h=r(10),x=r(!1),P=async()=>{try{const e=(await C.get(`${v.value}/api/jenisbarang`)).data;i.value=e.map((t,n)=>({jenisbarang_id:t.jenisbarang_id,jenisbarang_kode:t.jenisbarang_kode,jenisbarang_nama:t.jenisbarang_nama,jenisbarang_jumlah:t.jenisbarang_jumlah,created_at:t.created_at}))}catch(s){console.error("Error fetching data:",s)}};F(()=>{const s=H();v.value=s.public.apiBase,P(),_.value=!1});const D=w(()=>{const s=[...i.value].sort((t,n)=>new Date(n.created_at)-new Date(t.created_at));if(!g.value)return s;const e=g.value.toLowerCase();return s.filter(t=>{var n,l;return((n=t.jenisbarang_nama)==null?void 0:n.toLowerCase().includes(e))||((l=t.jenisbarang_kode)==null?void 0:l.toLowerCase().includes(e))})}),z=w(()=>{const s=(o.value-1)*h.value,e=s+h.value;return D.value.slice(s,e)}),y=w(()=>Math.ceil(D.value.length/h.value)),G=()=>{B.value=!0},M=()=>{B.value=!1},N=async()=>{if(x.value)return;x.value=!0;const s={no:i.value.length+1,jenisbarang_kode:d.value.jenisbarang_kode,jenisbarang_nama:d.value.jenisbarang_nama,jenisbarang_jumlah:0};try{const e=await C.post(`${v.value}/api/jenisbarang`,s);if(e.status===201){const t=e.data;i.value.push({no:i.value.length+1,jenisbarang_id:t.jenisbarang_id,jenisbarang_kode:t.jenisbarang_kode,jenisbarang_nama:t.jenisbarang_nama,jenisbarang_jumlah:0}),M(),await P(),d.value={jenisbarang_kode:"",jenisbarang_nama:"",jenisbarang_jumlah:0},await p.fire({title:"Berhasil!",text:"Produk berhasil ditambahkan.",icon:"success",timer:1500,showConfirmButton:!1})}}catch(e){console.error("Error adding product:",e),p.fire({title:"Gagal!",text:"Terjadi kesalahan saat menambahkan produk.",icon:"error"})}finally{x.value=!1}},O=s=>{j.value={...s},m.value=1,_.value=!0},$=()=>{_.value=!1,j.value=null},U=async()=>{j.value.jenisbarang_kode;const s=j.value.jenisbarang_id,e=m.value!==null?m.value:1;try{let t=[];t=(await C.post(`${v.value}/api/codebarang`,{jumlah_barang:e,code_jenisbarang_id:s})).data.data;const l=window.open("","","width=800,height=600");if(!l)return;l.document.write(`
      <!DOCTYPE html>
      <html>
        <head>
          <title>Print Barcode</title>
          <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
          <style>
            @media print {
              @page {
                size: A4 landscape;
                margin: 0;
              }
              body {
                margin: 0;
              }
              .page {
                page-break-after: always;
                width: 100vw;
                height: 100vh;
                position: relative;
              }
            }
            .barcode-container {
              position: absolute;
              bottom: 20px;
              right: 20px;
              text-align: center;
              font-size: 10px;
            }
          </style>
        </head>
        <body>
          ${t.map((q,A)=>`
            <div class="page">
              <div class="barcode-container">
                <div>${q.code_nama}</div>
                <svg id="barcode-${A}"></svg>
              </div>
            </div>
          `).join("")}

          <script>
            window.onload = function() {
              const barcodes = ${JSON.stringify(t)};
              for (let i = 0; i < barcodes.length; i++) {
                JsBarcode("#barcode-" + i, barcodes[i].code_nama, {
                  format: "CODE128",
                  lineColor: "#000",
                  width: 2.5,
                  height: 60,
                  displayValue: false
                });
              }
              window.print();
            }
          <\/script>
        </body>
      </html>
    `),l.document.close(),$(),await P()}catch(t){console.error("Gagal update jumlah code barang:",t),p.fire("Gagal","Gagal melakukan update jumlah barcode. Coba lagi","error")}},K=async(s,e)=>{if((await p.fire({title:"Konfirmasi Hapus",text:`Anda yakin ingin menghapus "${e}"?`,icon:"warning",showCancelButton:!0,confirmButtonText:"Ya, Hapus!",cancelButtonText:"Batal",reverseButtons:!0})).isConfirmed)try{(await C.delete(`${v.value}/api/jenisbarang/${s}`)).status===200&&(i.value=i.value.filter(l=>l.jenisbarang_id!==s),await p.fire({title:"Berhasil!",text:`"${e}" telah dihapus.`,icon:"success",timer:1500,showConfirmButton:!1}))}catch(n){console.error("Error deleting product:",n),p.fire({title:"Gagal",text:"Terjadi kesalahan saat menghapus data.",icon:"error"})}},L=w(()=>{const s=y.value,e=o.value,t=[];if(s<=5)for(let n=1;n<=s;n++)t.push(n);else e<=3?t.push(1,2,3,"...",s):e>=s-2?t.push(1,"...",s-2,s-1,s):t.push(1,"...",e-1,e,e+1,"...",s);return t});return S(g,()=>{o.value=1}),S(o,s=>{s<1&&(o.value=1),s>y.value&&(o.value=y.value)}),(s,e)=>(c(),u("div",null,[e[15]||(e[15]=a("title",null,"Menu Code",-1)),e[16]||(e[16]=a("div",{class:"judul text-xl font-semibold mb-4"},"Menu Code",-1)),a("div",R,[a("div",W,[f(a("input",{class:"search-box p-2 border rounded-md","onUpdate:modelValue":e[0]||(e[0]=t=>g.value=t),type:"text",placeholder:"Search barang..."},null,512),[[k,g.value]])]),a("div",null,[a("button",{class:"btn-add bg-blue-500 text-white text-center rounded-md hover:bg-blue-600 w-[104px] h-[25px]",onClick:G}," + Tambah ")])]),a("div",null,[a("table",X,[e[7]||(e[7]=a("thead",{class:"bg-blue-100"},[a("tr",null,[a("th",{class:"px-4 py-2 text-left"},"No."),a("th",{class:"px-4 py-2 text-left"},"Kode Barang"),a("th",{class:"px-4 py-2 text-left"},"Nama Barang"),a("th",{class:"px-4 py-2 text-left"},"Jumlah Barang"),a("th",{class:"px-4 py-2 text-left"},"Aksi")])],-1)),a("tbody",null,[(c(!0),u(V,null,J(z.value,(t,n)=>(c(),u("tr",{key:t.jenisbarang_id,class:E(n%2===0?"bg-white":"bg-gray-50")},[a("td",Z,b(n+1),1),a("td",ee,b(t.jenisbarang_kode),1),a("td",ae,b(t.jenisbarang_nama),1),a("td",te,b(t.jenisbarang_jumlah),1),a("td",se,[a("div",ne,[a("button",{class:"flex items-center gap-1 px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded-md text-s",onClick:l=>O(t)}," Print ",8,oe),a("button",{class:"flex items-center gap-1 px-2 py-1 bg-red-500 text-white hover:bg-red-600 rounded-md text-s",onClick:l=>K(t.jenisbarang_id,t.jenisbarang_kode)}," Delete ",8,le)])])],2))),128))])]),a("div",re,[a("div",ie,[e[9]||(e[9]=a("label",{for:"perPage"},"Tampilkan:",-1)),f(a("select",{id:"perPage","onUpdate:modelValue":e[1]||(e[1]=t=>h.value=t),class:"border px-2 py-1 rounded text-xs"},e[8]||(e[8]=[a("option",{value:5},"5",-1),a("option",{value:10},"10",-1),a("option",{value:20},"20",-1),a("option",{value:50},"50",-1)]),512),[[Y,h.value]])]),a("div",de,[a("button",{class:"px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs",disabled:o.value===1,onClick:e[2]||(e[2]=t=>o.value--)}," Sebelumnya ",8,ue),(c(!0),u(V,null,J(L.value,(t,n)=>(c(),u("button",{key:n,onClick:l=>typeof t=="number"&&(o.value=t),class:E(["px-3 py-1 rounded text-xs",o.value===t?"bg-blue-500 text-white":"bg-gray-200",t==="..."?"cursor-default":"cursor-pointer"]),disabled:t==="..."},b(t),11,ce))),128)),a("button",{class:"px-3 py-1 bg-gray-300 rounded hover:bg-gray-400 text-xs",disabled:o.value===y.value,onClick:e[3]||(e[3]=t=>o.value++)}," Selanjutnya ",8,be)])])]),B.value?(c(),u("div",pe,[a("div",ge,[e[12]||(e[12]=a("h3",{class:"text-xl font-semibold mb-4"},"Tambah Barang",-1)),a("form",{onSubmit:I(N,["prevent"])},[a("div",me,[e[10]||(e[10]=a("label",{for:"jenisbarang_kode",class:"block text-sm font-medium text-gray-700"},"Kode Barang",-1)),f(a("input",{"onUpdate:modelValue":e[4]||(e[4]=t=>d.value.jenisbarang_kode=t),type:"text",id:"jenisbarang_kode",maxlength:"5",class:"mt-1 block w-full border-[1px] pl-3 border-gray rounded-md shadow-sm w-[382px] h-[41px]",placeholder:" Masukkan kode barang",required:""},null,512),[[k,d.value.jenisbarang_kode]])]),a("div",ve,[e[11]||(e[11]=a("label",{for:"jenisbarang_nama",class:"block text-sm font-medium text-gray-700"},"Jenis Barang",-1)),f(a("input",{"onUpdate:modelValue":e[5]||(e[5]=t=>d.value.jenisbarang_nama=t),type:"text",id:"jenisbarang_nama",class:"mt-1 block w-full border-[1px] border-gray rounded-md shadow-sm pl-3 w-[382px] h-[41px]",placeholder:" Masukkan jenis barang",required:""},null,512),[[k,d.value.jenisbarang_nama]])]),a("div",he,[a("button",{type:"button",onClick:M,class:"mr-4 px-4 py-2 bg-[#D8D8D8] text-white rounded-md hover:bg-[#D8D8D8]"}," Cancel "),a("button",{type:"submit",disabled:x.value,class:"px-4 py-2 bg-[#1C9DBD] text-white rounded-md hover:bg-bg-[#1C9DBD]"},b(x.value?"Saving...":"Save"),9,xe)])],32)])])):T("",!0),_.value?(c(),u("div",fe,[a("div",_e,[e[14]||(e[14]=a("h3",{class:"text-lg font-semibold mb-4"},"Print Barcode",-1)),a("div",je,[e[13]||(e[13]=a("label",{class:"block text-sm font-medium text-gray-700"},"Jumlah Kode",-1)),f(a("input",{type:"number","onUpdate:modelValue":e[6]||(e[6]=t=>m.value=t),min:"1",class:"mt-1 block w-full border border-gray-300 rounded-md p-2",placeholder:"Masukkan jumlah kode"},null,512),[[k,m.value]])]),a("div",{class:"flex justify-end"},[a("button",{class:"mr-4 text-gray-500",onClick:$}," Cancel "),a("button",{class:"px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600",onClick:U}," Print ")])])])):T("",!0)]))}},Pe=Q(ye,[["__scopeId","data-v-e12e9a78"]]);export{Pe as default};

const nums=[{name:'wash clothes',date:'22-06-2026'},{name:'study hard',date:'20-07-2026'}];

renderToDoList();
function renderToDoList(){
          let htmlCombined='';
        nums.forEach(function(num,index){
            
          const name=num.name;
          const date=num.date;
          const htmldisplay=`<p>${name} ${date}<button onclick="nums.splice(${index},1); renderToDoList();">Delete</button></p>`;
          htmlCombined+=htmldisplay;

        });
         /* for(let i=0;i<nums.length;i++){
          const num=nums[i];
          const name=num.name;
          const date=num.date;
          const htmldisplay=`<p>${name} ${date}<button onclick="nums.splice(${i},1); renderToDoList();">Delete</button></p>`;
          htmlCombined+=htmldisplay;

          }*/
          console.log(htmlCombined);
          document.querySelector('.htmll').innerHTML=htmlCombined;

          }
function doFtn(){  
          const inputToDo=document.querySelector('.inputt');
          const nputToDo=inputToDo.value;

          const dateDue=document.querySelector('.dateInput');
          const dueDate=dateDue.value;

          nums.push({name:nputToDo,date:dueDate});

          inputToDo.value='';
          console.log(nums);

          renderToDoList();
}

function onKey(event){if (event.key=='Enter'){ doFtn();}
}




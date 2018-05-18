require('../css/start.css');

(function(){

  document.addEventListener("DOMContentLoaded", DOMReady);

  function DOMReady() {
    let leftMenu = document.querySelector('#js-cat-menu');
    leftMenu.addEventListener('click', onLeftMenuClick);
  }

  function onLeftMenuClick(e) {
    let trigger = e.target;
    e.preventDefault();
    if(e.target.nodeName === "A") {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', e.target.getAttribute("href"), true);
      // xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.send();
      xhr.onload = function (e) {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            let data = JSON.parse(xhr.responseText);
            todosParse(data);
            let allLi = trigger.closest("ul").getElementsByTagName('a');
            for(let i=0;i<allLi.length;i++) {
              allLi[i].classList.remove('uk-text-success');
            }
            trigger.classList.add("uk-text-success");
          } else {
            console.log(xhr.status + ': ' + xhr.statusText);
          }
        }
      };
    }
    return false;
  }

  function todosParse(data)
  {
    let todosList = document.querySelector('#js-todos-list');
    todosList.innerHTML = "";
    for(let i=0;i<data.length;i++) {
      let li = document.createElement("div");
      li.classList.add('uk-block');
      li.classList.add('uk-block-default');
      li.innerHTML = "<h2>" +
      data[i].title + "</h2>"+
      "<p>"+data[i].content+"</p>" +
      new Date(data[i].dateSheduled.timestamp*1000) +
      "<div class='uk-button-group'> <a class='uk-button uk-button-small uk-button-danger' href=''>delete</a> <a class='uk-button uk-button-small uk-button-primary' href=''>close</a> </div>";
      todosList.appendChild(li);
    }
  }

}());

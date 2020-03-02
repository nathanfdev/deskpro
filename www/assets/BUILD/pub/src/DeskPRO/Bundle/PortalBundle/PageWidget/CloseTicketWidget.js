import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class CloseTicketWidget extends PageWidget {
  renderWidget() {
    window.document.getElementById('closeTicketBtn').addEventListener('click', (e) => {
      const url = e.target.dataset.url;
      const $form = $(e.target).closest('form');
      const data = $form.serializeJSON();

      function reqListener() {
        if (this.status === 200 && this.responseURL.match(url)) {
          const doc = document.createDocumentFragment();
          const div = document.createElement('div');
          div.innerHTML = this.response;
          doc.appendChild(div);

          const header = window.document.querySelector('#closeConfirm .modal-header');
          while (header.firstChild) {
            header.removeChild(header.firstChild);
          }

          header.appendChild(doc.querySelector('.dp-po-title'));
          const body = window.document.querySelector('#closeConfirm .modal-body');
          while (body.firstChild) {
            body.removeChild(body.firstChild);
          }

          const children = doc.querySelector('.dp-po-ticket-modal-body').children;
          for (let i = 0; i < children.length; i++) {
            body.appendChild(children[i]);
          }

          window.document.querySelector('#closeConfirm .modal-footer').remove();
          window.dp_refresh_csrf_token();
        } else if (this.status === 200) {
          window.location.href = this.responseURL;
        }
      }

      const oReq = new XMLHttpRequest();
      oReq.open('POST', url);
      oReq.setRequestHeader('Content-type', 'application/json');
      oReq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      oReq.onload = reqListener.bind(oReq);
      oReq.send(JSON.stringify(data));
    });
  }
}

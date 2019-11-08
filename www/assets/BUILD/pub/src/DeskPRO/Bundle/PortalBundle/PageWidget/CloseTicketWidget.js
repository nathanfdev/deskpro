import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class CloseTicketWidget extends PageWidget {
  renderWidget() {
    window.document.getElementById('closeTicketBtn').addEventListener('click', (e) => {
      function reqListener() {
        if (this.status === 200) {
          window.location.href = this.responseURL;
        }
      }

      const oReq = new XMLHttpRequest();
      oReq.open('POST', e.target.dataset.url);
      oReq.onload = reqListener.bind(oReq);
      oReq.send();
    });
  }
}

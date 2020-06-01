import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class HelpcenterCCDelete extends PageWidget {
  renderWidget() {
    const $link = this.$element;

    $link.click((e) => {
      e.preventDefault();
      e.stopPropagation();

      const action = $link.attr('href');

      const id = action.replace(/.*\//, '');
      const count = document.querySelector('.dp-po-ticket-meta-cc .dp-po-ticket-meta-title .count');
      const newCount = parseInt(count.innerText, 10) - 1;
      count.innerText = newCount;
      const participant = document.querySelector(`.dp-po-ticket-meta-cc-item.participant-${id}`);
      participant.style.display = 'none';
      if (newCount === 0) {
        document.querySelector('.dp-po-ticket-meta-cc-item.none').style.display = 'flex';
      }

      function rollback() {
        if (newCount === 0) {
          document.querySelector('.dp-po-ticket-meta-cc-item.none').style.display = 'none';
        }
        count.innerText = newCount + 1;
        participant.style.display = 'flex';
      }

      const xhr = new XMLHttpRequest();

      xhr.addEventListener(' error', () => {
        rollback();
      });

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          try {
            const response = JSON.parse(xhr.response);
            if (response.data.success !== true) {
              rollback();
            }
          } catch (error) {
            rollback();
          }
        }
      };
      xhr.open('POST', action, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.send(null);

      return false;
    });
  }
}

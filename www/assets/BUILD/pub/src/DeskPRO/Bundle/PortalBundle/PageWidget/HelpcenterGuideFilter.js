import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';
import debounce from 'lodash/debounce';

export class HelpcenterGuideFilter extends PageWidget {
  renderWidget() {
    const input = this.$element;

    this.guides = $('h2.dp-po-guides-title');
    const debouncedFilter = debounce(this.filter, 200);
    input.on('keyup', (e) => {
      debouncedFilter(e.target.value);
    });
  }

  filter = (filter) => {
    if (filter) {
      this.guides.each((index) => {
        const guide = this.guides[index];
        if (!$(guide).text().toLowerCase().includes(filter.toLowerCase())) {
          $(guide).parents('.col-md-6').hide();
        } else {
          $(guide).parents('.col-md-6').show();
        }
      });
    } else {
      this.guides.each((index) => {
        $(this.guides[index]).parents('.col-md-6').show();
      });
    }
  }
}

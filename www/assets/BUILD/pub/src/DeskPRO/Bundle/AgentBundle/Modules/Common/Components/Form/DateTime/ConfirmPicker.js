import Picker from 'anytime';
import createButton from 'anytime/src/lib/create-button';

export class ConfirmPicker extends Picker {

  renderFooter = footerEl => {
    // 'Done' button
    const doneBtn = createButton(this.options.doneText, [
      'anytime-picker__button',
      'anytime-picker__button--done'
    ]);

    footerEl.appendChild(doneBtn);
    doneBtn.addEventListener('click', () => {
      this.hide();
      this.emit('done', null);
    });

    // 'Clear' button
    const clearBtn = createButton(this.options.clearText, [
      'anytime-picker__button',
      'anytime-picker__button--clear'
    ]);

    footerEl.appendChild(clearBtn);
    clearBtn.addEventListener('click', () => {
      this.update(null);
      this.hide();
    });
  };
}

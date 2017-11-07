import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from '@deskpro/react-scrollbar';
import classNames from 'classnames';

class SemanticMultiSelect extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    choices:  PropTypes.array,
    onChange: PropTypes.func
  };

  onToggleAll = () => {
    const { value, choices, onChange } = this.props;

    if (!choices) {
      return;
    }

    const newValue = [];
    if (!value || value.length !== choices.length) {
      choices.forEach((choice) => {
        newValue.push(choice.value);
      });
    }

    onChange(newValue);
  };

  onChange = (item) => {
    const { value = [], onChange } = this.props;
    const index = value.indexOf(item);

    if (index !== -1) {
      value.splice(index, 1);
    } else {
      value.push(item);
    }

    onChange(value);
  };

  render() {
    const { choices, value } = this.props;

    return (
      <div>
        <span onClick={this.onToggleAll} className="multi-select-toggle-all">
          Toggle all
        </span>
        <ScrollArea className="multi-select" vertical>
          {choices.map((choice, index) => {
            const checked = value.indexOf(choice.value) !== -1;

            return (
              <div key={index} onClick={() => this.onChange(choice.value)}>
                <div className={classNames('ui', { checked }, 'checkbox')}>
                  <input type="checkbox" checked={checked ? 'checked' : ''} className="hidden" />
                  <label htmlFor="checkbox">{choice.label}</label>
                </div>
              </div>
            );
          })}
        </ScrollArea>
      </div>
    );
  }
}

export default SemanticMultiSelect;

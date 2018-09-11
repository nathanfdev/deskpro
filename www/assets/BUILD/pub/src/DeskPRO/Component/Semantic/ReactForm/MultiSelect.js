import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';

class SemanticMultiSelect extends React.Component {

  static propTypes = {
    value:         PropTypes.array,
    choices:       PropTypes.array,
    onChange:      PropTypes.func,
    toggleAll:     PropTypes.bool,
    uncheckAll:    PropTypes.bool,
    selectedCount: PropTypes.bool,
  };

  static defaultProps = {
    toggleAll: true,
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

  toggleAll = () => {
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

  uncheckAll = () => {
    const { choices, onChange } = this.props;
    if (!choices) {
      return;
    }

    onChange([]);
  };

  render() {
    const { choices = [], value, selectedCount, toggleAll, uncheckAll } = this.props;
    const primaryChoices = choices.filter(choice => choice.primary);
    const otherChoices = choices.filter(choice => !choice.primary);
    const renderChoice = (choice, index) => {
      const checked = value && value.indexOf(choice.value) !== -1;

      return (
        <div
          key={index}
          onClick={() => {
            if (!choice.disabled) {
              this.onChange(choice.value);
            }
          }}
        >
          <div className={classNames('ui', { checked, disabled: choice.disabled }, 'checkbox')}>
            <input type="checkbox" checked={checked ? 'checked' : ''} className="hidden" />
            <label htmlFor="checkbox">{choice.label}</label>
          </div>
        </div>
      );
    };

    return (
      <div>
        { toggleAll ? <span onClick={this.toggleAll} className="multi-select-toggle-all">Toggle all</span> : null }
        { uncheckAll ? <span onClick={this.uncheckAll} className="multi-select-toggle-all">Uncheck all</span> : null }
        { selectedCount && <span className="multi-select-count">Selected: {value && value.length}</span> }
        <ScrollArea className="multi-select" vertical>
          {primaryChoices.length > 0 &&
          <div className="multi-select-primary-options">
            {primaryChoices.map(renderChoice)}
          </div>}
          {otherChoices.map(renderChoice)}
        </ScrollArea>
      </div>
    );
  }
}

export default SemanticMultiSelect;

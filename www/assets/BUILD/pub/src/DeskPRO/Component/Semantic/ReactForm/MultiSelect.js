import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import { SortableContainer, SortableElement, arrayMove } from 'react-sortable-hoc';
import classNames from 'classnames';
import Immutable from 'immutable';
import $ from 'jquery';

class SemanticMultiSelect extends React.Component {

  static propTypes = {
    value:         PropTypes.array,
    choices:       PropTypes.array,
    onChange:      PropTypes.func,
    toggleAll:     PropTypes.bool,
    uncheckAll:    PropTypes.bool,
    selectedCount: PropTypes.bool
  };

  static defaultProps = {
    toggleAll: true,
  };

  onChange = (item) => {
    const { value = [], onChange } = this.props;
    const immutableValue = Immutable.fromJS(value);
    const immutableItem = Immutable.fromJS(item);

    if (immutableValue.includes(immutableItem)) {
      value.splice(immutableValue.indexOf(immutableItem), 1);
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
    const { choices = [], value, onChange, selectedCount, toggleAll, uncheckAll } = this.props;
    const primaryChoices = choices.filter(choice => choice.primary);
    const otherChoices = choices.filter(choice => !choice.primary);

    const primarySortableChoices = primaryChoices.filter(choice => choice.sortable);
    const primaryUnsortableChoices = primaryChoices.filter(choice => !choice.sortable);

    const otherSortableChoices = otherChoices.filter(choice => choice.sortable);
    const otherUnsortableChoices = otherChoices.filter(choice => !choice.sortable);

    const hasSortable = primarySortableChoices.length > 0 || otherSortableChoices.length > 0;

    const immutableValue = Immutable.fromJS(value);
    const renderChoice = (choice, index) => {
      const immutableItem = Immutable.fromJS(choice.value);
      const checked = value && immutableValue.indexOf(immutableItem) !== -1;

      return (
        <div
          key={index}
          onClick={() => {
            if (!choice.disabled) {
              this.onChange(choice.value);
            }
          }}
          className={classNames({ sortable: choice.sortable })}
        >
          <span className={classNames({ 'multi-sortable-icon': hasSortable })}>
            {choice.sortable && <i className="fa fa-bars drag-handle" />}
          </span>
          <div className={classNames('ui', { checked, disabled: choice.disabled }, 'checkbox')}>
            <input type="checkbox" checked={checked ? 'checked' : ''} className="hidden" />
            <label htmlFor="checkbox">
              {choice.label}
            </label>
          </div>
        </div>
      );
    };

    const SortableItem = SortableElement(({ item, index }) => renderChoice(item, index));
    const SortableList = SortableContainer(({ items }) => (
      <div>
        {items.map((item, index) => (
          <SortableItem key={`item-${index}`} index={index} item={item} />
        ))}
      </div>
    ));

    const sortPrimaryChoices = ({ oldIndex, newIndex }) => {
      const newVal = [
        ...arrayMove(primarySortableChoices, oldIndex, newIndex),
        ...primaryUnsortableChoices,
        ...otherChoices
      ].map(choice => choice.value).filter(item => immutableValue.includes(Immutable.fromJS(item)));

      onChange(newVal);
    };

    const sortOtherChoices = ({ oldIndex, newIndex }) => {
      const newVal = [
        ...primaryChoices,
        ...arrayMove(otherSortableChoices, oldIndex, newIndex),
        ...otherUnsortableChoices
      ].map(choice => choice.value).filter(item => immutableValue.includes(Immutable.fromJS(item)));

      onChange(newVal);
    };

    const shouldCancelStart = event => $(event.target).attr('for') === 'checkbox';

    return (
      <div>
        { toggleAll ? <span onClick={this.toggleAll} className="multi-select-toggle-all">Toggle all</span> : null }
        { uncheckAll ? <span onClick={this.uncheckAll} className="multi-select-toggle-all">Uncheck all</span> : null }
        { selectedCount && <span className="multi-select-count">Selected: {value && value.length}</span> }
        <ScrollArea className="multi-select" vertical>
          {primaryChoices.length > 0 &&
          <div className="multi-select-primary-options">
            <SortableList items={primarySortableChoices} shouldCancelStart={shouldCancelStart} onSortEnd={sortPrimaryChoices} />
            {primaryUnsortableChoices.map(renderChoice)}
          </div>}
          <SortableList items={otherSortableChoices} shouldCancelStart={shouldCancelStart} onSortEnd={sortOtherChoices} />
          {otherUnsortableChoices.map(renderChoice)}
        </ScrollArea>
      </div>
    );
  }
}

export default SemanticMultiSelect;

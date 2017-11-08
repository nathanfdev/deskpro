import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import classNames from 'classnames';
import uniqueId from 'lodash/uniqueId';

class SelectOption extends React.Component {

  static propTypes = {
    onClickOption: PropTypes.func,
    disabled:      PropTypes.bool,
    displayDepth:  PropTypes.number,
    isFocused:     PropTypes.bool,
    option:        PropTypes.object.isRequired,
    multiple:      PropTypes.bool,
    active:        PropTypes.bool
  };

  onClickOption = (event) => {
    event.preventDefault();
    const { disabled, onClickOption, option } = this.props;

    // do not fire events for disabled options
    if (!disabled) {
      onClickOption(option);
    }
  };

  render() {
    const { option, isFocused, active, disabled, displayDepth, multiple } = this.props;

    return (
      <li className={classNames({ focused: isFocused, 'select-option-disabled': disabled })}>
        <a
          onClick={this.onClickOption}
          className={classNames({ active, [`display-depth-${displayDepth}`]: displayDepth > 0 })}
        >
          {multiple && !disabled
            ? <span className={classNames('checkbox', { checked: active })}>
              <i className="fa fa-check" />
            </span>
            : null
          }
          <span className="option-title">{option.title}</span>
        </a>
      </li>
    );
  }
}

export default class PortalSimpleSelectBox extends React.Component {

  static propTypes = {
    widgetOptions: PropTypes.object,
    multiple:      PropTypes.bool,
    expanded:      PropTypes.bool,
    level:         PropTypes.number,
    value:         PropTypes.any, // eslint-disable-line react/forbid-prop-types
    options:       PropTypes.any, // eslint-disable-line react/forbid-prop-types
    onChange:      PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      id:             uniqueId('selectbox_'),
      options:        props.options,
      visibleOptions: props.options,
      filterText:     '',
      selectedOption: null,
      value:          props.multiple ? (props.value || []) : props.value,
      expanded:       props.expanded || false,
      level:          props.level || 1
    };
  }

  componentWillReceiveProps(nextProps) {
    if (this.state.options !== nextProps.options) {
      this.setState({
        options:        nextProps.options,
        visibleOptions: nextProps.options,
        filterText:     '',
        selectedOption: null,
        value:          nextProps.multiple ? (nextProps.value || []) : nextProps.value,
        level:          nextProps.level || 1
      });
    } else if (nextProps.value && this.state.value !== nextProps.value) {
      this.setState({
        value: nextProps.multiple ? (nextProps.value || []) : nextProps.value,
      });
    }
  }

  onClickHeader = () => {
    if (!this.state.expanded) {
      this.toggleExpanded();
    }
  };

  onKeyDown = (event) => {
    if (event.keyCode === 32) {
      event.preventDefault();
      this.toggleExpanded();
    }
  };

  onClickOut = () => {
    if (this.state.expanded) {
      this.toggleExpanded();
    }
  };

  onClickOption = (option) => {
    this.changeToOption(option);
    if (this.state.expanded && !this.props.multiple) {
      this.toggleExpanded();
    }
  };

  changeToOption(option) {
    if (!option) {
      return;
    }

    let val;
    if (this.props.multiple) {
      val = this.state.value;
      if (!val.some(v => parseInt(v.id, 10) === parseInt(option.id, 10))) {
        val.push(option);
      } else {
        val = val.filter(opt => parseInt(opt.id, 10) !== parseInt(option.id, 10));
      }
      val = val.filter(v => typeof v !== 'undefined');
    } else {
      val = option;
    }

    this.setState({
      value:    val,
      expanded: this.props.multiple
    });
    this.props.onChange(val);
  }

  focusedOnKeyDown = (event) => {
    if (event.keyCode === 13) { // enter
      event.preventDefault();
      this.openMenu();
    }
  };

  filterNav = (ev) => {
    const key = ev.keyCode;
    switch (key) {
      case 13: // enter
        ev.preventDefault();
        if (this.state.selectedOption) {
          this.changeToOption(this.state.selectedOption, true);
        }
        break;
      case 38: // up
        ev.preventDefault();
        this.openMenu();
        this.doSelectPrev();
        break;
      case 9: // tab, stay inside if we're focused on the input box, otherwise ignore
        if (this.state.expanded) {
          ev.preventDefault();
          this.openMenu();
        }
        break;
      case 40: // down
        ev.preventDefault();
        this.openMenu();
        this.doSelectNext();
        break;
      case 27: // escape
        ev.preventDefault();
        this.closeMenu();
        break;
      default:
        break;
    }
  };

  filterChange = () => {
    // no change
    if (this.filterInput.value === this.state.filterText) {
      return;
    }

    const getFitleredOptions = (rawFilterText, options) => {
      if (rawFilterText) {
        const filterText = rawFilterText.toLowerCase();
        return options.filter(opt => opt.title.toLowerCase().indexOf(filterText) !== -1);
      }

      return options;
    };

    this.openMenu();
    const visibleOptions = getFitleredOptions(this.filterInput.value, this.state.options);
    // current selection if its still visible, or the first result (or nothing if list is empty)
    const selectedOption = visibleOptions.indexOf(this.state.selectedOption) !== -1
      ? this.state.selectedOption
      : visibleOptions[0] || null;

    this.setState({
      visibleOptions,
      selectedOption,
      filterText: this.filterInput.value
    });
  };

  doSelectNext() {
    if (!this.state.visibleOptions.length) {
      return;
    }

    if (this.state.selectedOption === null) {
      this.setState({ selectedOption: this.state.visibleOptions[0] });
    } else {
      let next = null;
      for (let i = 0; i < this.state.visibleOptions.length; i += 1) {
        if (this.state.visibleOptions[i] === this.state.selectedOption) {
          if (this.state.visibleOptions[i + 1]) {
            next = this.state.visibleOptions[i + 1];
          } else {
            next = this.state.visibleOptions[0]; // wrap
          }
        }
      }

      this.setState({
        selectedOption: next
      });
    }
  }

  doSelectPrev() {
    if (!this.state.visibleOptions.length) {
      return;
    }

    if (this.state.selectedOption === null) {
      this.setState({ selectedOption: this.state.visibleOptions[this.state.visibleOptions.length - 1] });
    } else {
      let next = null;
      for (let i = 0; i < this.state.visibleOptions.length; i += 1) {
        if (this.state.visibleOptions[i] === this.state.selectedOption) {
          if (i > 0) {
            next = this.state.visibleOptions[i - 1];
          } else {
            next = this.state.visibleOptions[this.state.visibleOptions.length - 1]; // wrap
          }
        }
      }

      this.setState({
        selectedOption: next
      });
    }
  }

  closeMenu() {
    if (this.state.expanded) {
      this.toggleExpanded();
    }
  }

  openMenu() {
    if (!this.state.expanded) {
      this.toggleExpanded();
    }
  }

  toggleExpanded() {
    this.setState({
      expanded:       !this.state.expanded,
      selectedOption: null,
      visibleOptions: this.state.options
    });
  }

  renderStaticHeader() {
    const { multiple, options } = this.props;
    const classes = ['default'];
    if (this.state.expanded) {
      classes.push('expanded');
    } else {
      classes.push('collapsed');
    }
    if (this.state.value) {
      classes.push('with-value');
    } else {
      classes.push('with-no-value');
    }

    const className = classes.join(' ');

    if (!this.state.expanded && (multiple ? this.state.value.length > 0 : this.state.value)) {
      return (
        <div
          className={className}
          onClick={this.onClickHeader}
          onKeyDown={this.focusedOnKeyDown}
          tabIndex="0"
          role="combobox"
          aria-expanded
        >
          <span className={`multiselect-title_${this.state.id}`}>
            {multiple
              ? this.state.value.map(opt => opt.title || <span>&nbsp;</span>).join(', ')
              : this.state.value.title || <span>&nbsp;</span>
            }
          </span>
          <i className={classNames('fa', 'fa-caret-down', `caret-down_${this.state.id}`)} />
        </div>
      );
    }

    return (
      <div className={className} onClick={this.onClickHeader}>
        <div className="filter-box">
          {options && options.length > 0
            ? <input
              type="text"
              placeholder={
                options.length > 8
                ? portalPhrases.get('portal.general.select_search_placeholder')
                : portalPhrases.get('portal.general.select_placeholder')
              }
              ref={(c) => { this.filterInput = c; }}
              onKeyDown={this.filterNav}
              onKeyUp={this.filterChange}
            />
            : <input
              type="text"
              disabled
              placeholder={portalPhrases.get('portal.general.select_placeholder')}
              onKeyDown={this.filterNav}
              onKeyUp={this.filterChange}
            />
          }
          <i className={classNames('fa', 'fa-caret-down', `caret-down_${this.state.id}`)} />
        </div>
      </div>
    );
  }

  renderDropdownList() {
    if (!this.state.expanded) {
      return null;
    }

    const options = this.state.visibleOptions;
    const isNullOption = option => option === null || !option.id || option.title === '';
    const isActive = (option) => {
      if (!this.state.value) {
        return false;
      } else if (this.props.multiple) {
        return this.state.value.includes(option);
      }

      return this.state.value.id === option.id;
    };

    return (
      <div className="options-wrapper">
        <ul>
          {options.length > 0
            ? options.map((option) => {
              if (isNullOption(option)) {
                return null;
              }

              return (
                <SelectOption
                  onClickOption={this.onClickOption}
                  disabled={option.children && option.children.length > 0}
                  displayDepth={option.depth}
                  isFocused={option === this.state.selectedOption}
                  key={option.id}
                  option={option}
                  multiple={!!this.props.multiple}
                  active={isActive(option)}
                />
              );
            })
            : <SelectOption
              onClickOption={this.onClickOption}
              disabled
              isFocused={false}
              key={0}
              option={{ title: `No matches found for ${this.state.filterText}`, id: 0 }}
              multiple={!!this.props.multiple}
              active={false}
            />
          }
        </ul>
      </div>
    );
  }

  render() {
    const { widgetOptions = {} } = this.props;
    const context = widgetOptions.context || [document];

    return (
      <div className={classNames('multiselect', widgetOptions.widgetClassName || null, `level-${this.state.level}`)}>
        <ClickOut
          onClickOut={this.onClickOut}
          additionalNodes={[`.multiselect-title_${this.state.id}`, `.caret-down_${this.state.id}`]}
          context={context}
        >
          {this.renderStaticHeader()}
          {this.renderDropdownList()}
        </ClickOut>
      </div>
    );
  }
}

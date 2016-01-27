import React, { PropTypes } from 'react';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import classNames from 'classnames';
import _ from 'lodash';

class SelectOption extends React.Component {

  static propTypes = {
    onClickOption: PropTypes.func,
    disabled: PropTypes.bool,
    displayDepth: PropTypes.number,
    isFocused: PropTypes.bool,
    option: PropTypes.object.isRequired,
    multiple: PropTypes.bool,
    active: PropTypes.bool
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
      <li ref="row"
          className={classNames({
            'focused': isFocused,
            'select-option-disabled': disabled
          })}>

        <a onClick={this.onClickOption}
           className={classNames({
             'active': active,
             [`display-depth-${displayDepth}`]: displayDepth > 0
           })}>

          {multiple && !disabled
            ? <span className={classNames('checkbox', {'checked': active})}>
                <i className="fa fa-check"></i>
              </span>
            : null
          }
          <span className="option-title">{option.title}</span>
        </a>
      </li>
    );
  }
}

export class PortalSimpleSelectBox extends React.Component {

  static propTypes = {
    widgetOptions: PropTypes.object,
    multiple: PropTypes.bool,
    expanded: PropTypes.bool,
    level: PropTypes.number,
    value: PropTypes.any,
    options: PropTypes.any,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      id: _.uniqueId('selectbox_'),
      options: props.options,
      visibleOptions: props.options,
      filterText: '',
      selectedOption: null,
      value: props.multiple ? (props.value || []) : props.value,
      expanded: props.expanded || false,
      level: props.level || 1
    };
  }

  componentWillReceiveProps(nextProps) {
    if (this.state.options !== nextProps.options) {
      this.setState({
        options: nextProps.options,
        visibleOptions: nextProps.options,
        filterText: '',
        selectedOption: null,
        value: nextProps.multiple ? (nextProps.value || []) : nextProps.value,
        expanded: nextProps.expanded || false,
        level: nextProps.level || 1
      });
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (this.refs.filterInput) {
      this.refs.filterInput.focus();
    } else if (this.refs.defaultRow && this.state.expanded !== prevState.expanded) {
      this.refs.defaultRow.focus();
    }
  }

  onClickHeader = () => {
    if (!this.state.expanded) {
      this.toggleExpanded();
    }
  };

  onKeyDown = event => {
    if (event.keyCode === 32) {
      event.preventDefault();
      this.toggleExpanded();
    }
  };

  onClickOut = () => {
    if (!this.state.expanded) {
      return;
    }
    if (this.refs.filterInput) {
      this.refs.filterInput.blur();
    }

    this.setState({
      expanded: false
    });
  };

  onClickOption = option => {
    this.changeToOption(option);
    if (this.state.expanded) {
      this.toggleExpanded();
    }
  };

  getFitleredOptions(rawFilterText, options) {
    if (rawFilterText) {
      const filterText = rawFilterText.toLowerCase();
      return options.filter(opt => opt.title.toLowerCase().indexOf(filterText) !== -1);
    }

    return options;
  }

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
      val = val.filter((v) => {
        return typeof v !== 'undefined';
      });
    } else {
      val = option;
    }

    this.setState({
      value: val,
      expanded: this.props.multiple ? true : false
    });
    this.props.onChange(val);
  }

  focusedOnKeyDown = event => {
    if (event.keyCode === 13) { // enter
      event.preventDefault();
      this.openMenu();
    }
  };

  isNullOption(option) {
    return option === null || !option.id || option.title === '';
  }

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
    if (this.refs.filterInput.value === this.state.filterText) {
      return;
    }

    this.openMenu();
    const visibleOptions = this.getFitleredOptions(this.refs.filterInput.value, this.state.options);
    // current selection if its still visible, or the first result (or nothing if list is empty)
    const selectedOption = visibleOptions.indexOf(this.state.selectedOption) !== -1 ? this.state.selectedOption : visibleOptions[0] || null;

    this.setState({
      visibleOptions: visibleOptions,
      selectedOption: selectedOption,
      filterText: this.refs.filterInput.value
    });
  };

  doSelectNext() {
    if (!this.state.visibleOptions.length) {
      return;
    }

    if (this.state.selectedOption === null) {
      this.setState({selectedOption: this.state.visibleOptions[0]});
    } else {
      let next = null;
      for (let i = 0; i < this.state.visibleOptions.length; i++) {
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
      this.setState({selectedOption: this.state.visibleOptions[this.state.visibleOptions.length - 1]});
    } else {
      let next = null;
      for (let i = 0; i < this.state.visibleOptions.length; i++) {
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
      expanded: !this.state.expanded,
      selectedOption: null,
      visibleOptions: this.state.options
    });
  }

  renderStaticHeader() {
    const { multiple } = this.props;
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
        <div className={className}
             onClick={this.onClickHeader}
             onKeyDown={this.focusedOnKeyDown}
             tabIndex="0"
             role="combobox"
             ref="defaultRow">

          <span className={`multiselect-title_${this.state.id}`}>
            {multiple
              ? this.state.value.map(opt => opt.title || <span>&nbsp;</span>).join(', ')
              : this.state.value.title || <span>&nbsp;</span>
            }
          </span>
          <i className="fa fa-caret-down" />
        </div>
      );
    }

    return (
      <div className={className} onClick={this.onClickHeader}>
        <div className="filter-box">
          <input type="text"
                 placeholder={PortalPhrases.get('portal.general.select_placeholder')}
                 ref="filterInput"
                 onKeyDown={this.filterNav}
                 onKeyUp={this.filterChange} />
        </div>
      </div>
    );
  }

  renderDropdownList() {
    if (!this.state.expanded) {
      return null;
    }

    const options = this.state.visibleOptions;
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
          {options.map((option) => {
            if (this.isNullOption(option)) {
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
                active={isActive(option)} />
            );
          })}
        </ul>
      </div>
    );
  }

  render() {
    const { widgetOptions = {} } = this.props;
    const context = widgetOptions.context || document;

    return (
      <ClickOut onClickOut={this.onClickOut}
                additionalNodes={[`.multiselect-title_${this.state.id}`]}
                context={context}>

        <div className={classNames('multiselect', `level-${this.state.level}`)}>
            {this.renderStaticHeader()}
            {this.renderDropdownList()}
        </div>
      </ClickOut>
    );
  }
}

import $ from "jquery";
import React, { PropTypes } from 'react';


//######################################################################################################################
//# SelectOption
//######################################################################################################################

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

  onClickOption = (ev) => {
    ev.preventDefault();
    // do not fire events for disabled options
    if (!this.props.disabled) {
      this.props.onClickOption(this.props.option);
    }
  }

  render() {
    const option = this.props.option;
    const isFocused = this.props.isFocused;
    return (
      <li
          ref="row"
          className={(isFocused ? 'focused ' : '') + (this.props.disabled ? "select-option-disabled" : '')}>
        <a
            onClick={this.onClickOption}
            className={(this.props.active ? 'active ' : '') + (this.props.displayDepth > 0 ? 'display-depth-'+this.props.displayDepth : '')}>
          {this.props.multiple && !this.props.disabled ? (
              <span className={"checkbox" + (this.props.active ? " checked" : "")}><i className="fa fa-check"></i></span>
          ) : null}
          <span
              className="option-title">{option.title}</span>
        </a>
      </li>
    );
  }
}


//######################################################################################################################
//# PortalSimpleSelectBox
//######################################################################################################################

export default class PortalSimpleSelectBox extends React.Component {
  constructor(props) {
    super(props);
    this.selRefCounter = 0;
    this.state = {
      options: props.options,
      visibleOptions: props.options,
      filterText: "",
      selectedOption: null,
      value: this.props.multiple ? (props.value || []) : props.value,
      expanded: this.props.expanded || false,
      level: props.level || 1
    }
  }

  getFitleredOptions(rawFilterText, options) {
    if (rawFilterText) {
      const filterText = rawFilterText.toLowerCase();
       return options.filter(opt => opt.title.toLowerCase().indexOf(filterText) !== -1);
    } else {
      return options;
    }
  }

  componentDidMount() {
    document.addEventListener("click", this.documentClickHandler.bind(this));
  }

  componentWillUnmount() {
    document.removeEventListener("click", this.documentClickHandler.bind(this));
  }

  documentClickHandler() {
    if (!this.state.expanded) {
      return;
    }
    this.setState({
      expanded: false
    });
    if (this.refs.filterInput) {
      this.refs.filterInput.blur();
    }
  }

  dropdownClickHandler(e) {
    e.nativeEvent.stopImmediatePropagation();
  }

  onClickOption(option, doClose = false) {
    this.changeToOption(option);
    if (doClose && this.state.expanded) {
      this.toggleExpanded();
    }
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


  renderStaticHeader() {
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

    if (!this.state.expanded && (this.props.multiple ? this.state.value.length > 0 : this.state.value)) {
      return (
        <div className={className} onClick={this.onClickHeader} tabIndex="0" role="combobox" ref="defaultRow">
          <span>{this.props.multiple ? (
              this.state.value.map((opt) => {
                return opt.title || (<span>&nbsp;</span>)
              }).join(', ')
          ) : this.state.value.title || (<span>&nbsp;</span>)}</span>
          <i className="fa fa-caret-down" />
        </div>
      );
    } else {
      return (
        <div className={className} onClick={this.onClickHeader}>
          <div className="filter-box">
            <input type="text" placeholder="Select..." ref="filterInput" onKeyDown={this.filterNav} onKeyUp={this.filterChange} />
          </div>
          <i className="fa fa-times" onClick={this.selectNullOption.bind(this)} />
        </div>
      );
    }
  }

  selectNullOption() {
    if (this.props.multiple) {
      // multiple, clear selected and close the expanded view
      this.setState({
        value: [],
        expanded: false
      });
      this.props.onChange([]);
    } else {
      // singular, select the null option
      const null_option = this.getFirstNullOption();
      if (null_option) {
        this.onClickOption(null_option, true);
      }
    }
  }

  isNullOption(option) {
    return option === null || !option.id || option.title === '';
  }

  getFirstNullOption() {
    return this.state.visibleOptions.find((option) => {
      return this.isNullOption(option);
    });
  }

  renderDropdownList() {
    if (!this.state.expanded) {
      return null;
    }

    const options = this.state.visibleOptions;

    const isActive = (option) => {
      if (!this.state.value) {
        return false;
      }

      if (this.props.multiple) {
        return this.state.value.includes(option);
      } else {
        return this.state.value.id === option.id;
      }
    };


    return (
      <ul onClick={this.dropdownClickHandler.bind(this)}>
        {options.map((option) => {
          if (this.isNullOption(option)) return null;
          return (
            <SelectOption onClickOption={this.onClickOption.bind(this)}
                          disabled={option.children && option.children.length > 0}
                          displayDepth={option.depth}
                          isFocused={option === this.state.selectedOption}
                          key={option.id}
                          option={option}
                          multiple={!!this.props.multiple}
                          active={isActive(option)}/>
          );
        })
        }
      </ul>);
  }

  componentDidUpdate(prevProps, prevState) {
    if (this.refs.filterInput) {
      this.refs.filterInput.focus();
    }
  }

  filterNav= (ev) => {
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
    }
  }

  filterChange = (ev) => {
    // no change
    if (this.refs.filterInput.value == this.state.filterText) {
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
  }

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
          if (this.state.visibleOptions[i+1]) {
            next = this.state.visibleOptions[i+1];
          } else {
            next = this.state.visibleOptions[0]; //wrap
          }
        }
      }
      this.setState({ selectedOption: next });
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
            next = this.state.visibleOptions[i-1];
          } else {
            next = this.state.visibleOptions[this.state.visibleOptions.length - 1]; //wrap
          }
        }
      }
      this.setState({ selectedOption: next });
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

  onClickHeader = (ev) => {
    if (!this.state.expanded) {
      this.toggleExpanded();
    }
    this.dropdownClickHandler(ev);
  }

  onKeyDown = (ev) => {
    const key = ev.keyCode;
    switch (key) {
      case 32:
        ev.preventDefault();
        this.toggleExpanded();
        break;
    }
  }

  render() {
    return (
        <div className={"multiselect level-" + this.state.level} onClick={this.dropdownClickHandler.bind(this)}>
          {this.renderStaticHeader()}
          {this.renderDropdownList()}
        </div>
    );
  }
}

import _ from "lodash";
import $ from "jquery";
import React from "react";


//######################################################################################################################
//# React Component
//######################################################################################################################

class SelectOption extends React.Component {
  onClickOption = (ev) => {
    ev.preventDefault();
    // do not fire events for disabled options
    if (!this.props.disabled) {
      this.props.onClickOption(this.props.option);
    }
  }

  onKeyDown = (ev) => {
    const key = ev.keyCode;
    switch (key) {
      case 32: // spave
      case 13: // enter
        ev.preventDefault();
        this.props.onClickOption(this.props.option, key === 13);
        break;
      case 38: // up
        ev.preventDefault();
        if (this.props.kbdNav) {
          this.props.kbdNav.focusPrev(this);
        }
        break;
      case 9: // tab - override tab so we stay within our own list
      case 40: // down
        ev.preventDefault();
        if (this.props.kbdNav) {
          this.props.kbdNav.focusNext(this);
        }
        break;
      case 27: // escape
        ev.preventDefault();
        if (this.props.kbdNav) {
          this.props.kbdNav.close(this);
        }
        break;
    }
  }

  focus() {
    if (this.refs.row) {
      this.refs.row.focus();
    }
  }

  render() {
    const option = this.props.option;
    return (
      <li
          tabIndex="0"
          onMouseOver={this.focus.bind(this)}
          onKeyDown={this.onKeyDown}
          role="option"
          ref="row"
          className={this.props.disabled ? "select-option-disabled" : null}>
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

export default class PortalSimpleSelectBox extends React.Component {
  constructor(props) {
    super(props);
    this.selRefCounter = 0;
    this.state = {
      options: props.options,
      value: this.props.multiple ? (props.value || []) : props.value,
      expanded: this.props.expanded || false,
      level: props.level || 1
    }
  }

  componentDidMount() {
    document.addEventListener("click", this.documentClickHandler.bind(this));
  }

  componentWillUnmount() {
    document.removeEventListener("click", this.documentClickHandler.bind(this));
  }

  documentClickHandler() {
    this.setState({
      expanded: false
    });
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
      if (!_.some(val, (v) => {
            return _.parseInt(v.id) === _.parseInt(option.id);
          })) {
        val.push(option);
      } else {
        val = val.filter((opt) => {
          return _.parseInt(opt.id) !== _.parseInt(option.id);
        });
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
        <div className={className} onClick={this.onClickHeader} onKeyDown={this.onKeyDown} tabIndex="0" role="combobox" ref="defaultRow">
          <span>{this.props.multiple ? (
              this.state.value.map((opt) => {
                return opt.title
              }).join(', ')
          ) : this.state.value.title}</span>
          <i className="fa fa-caret-down"></i>
        </div>
      );
    } else {
      return (
        <div className={className} onClick={this.onClickHeader} onKeyDown={this.onKeyDown} tabIndex="0" role="combobox" ref="defaultRow">
          <span>Select...</span>
          <i className="fa fa-caret-down"></i>
        </div>
      );
    }
  }

  renderDropdownList() {
    if (!this.state.expanded) {
      return null;
    }

    const kbdNav = {
      focusNext: this.doSelectNext.bind(this),
      focusPrev: this.doSelectPrev.bind(this),
      close: () => {
        this.closeMenu();
      }
    };

    return (
      <ul onClick={this.dropdownClickHandler.bind(this)} aria-expanded="true" role="listbox">
        {this.state.options.map((option) => {
          return (
            <SelectOption onClickOption={this.onClickOption.bind(this)}
                          disabled={option.children && option.children.length > 0}
                          displayDepth={option.depth}
                          kbdNav={kbdNav}
                          ref={'SelectOption_' + this.selRefCounter++}
                          key={option.id}
                          option={option}
                          multiple={this.props.multiple}
                          active={this.props.multiple ? _.includes(this.state.value, option) : this.state.value == option }/>
          );
        })
        }
      </ul>);
  }

  componentDidUpdate(prevProps, prevState) {
    // switch focus to the first item in menu
    if (!prevState.expanded) {
      const options = this.getOptions();
      if (options[0]) {
        options[0].focus();
      }
    } else if (!this.state.expanded && prevState.expanded) {
      this.refs['defaultRow'].focus();
    }
  }

  // Array of options
  // Not necessary named 0,1,2,3 etc because opts may be re-rendered,
  // so the ints in the name may change.
  getOptions() {
    return Object.keys(this.refs).map(k => {
      if (k.indexOf('SelectOption_') !== 0) {
        return null;
      }
      return this.refs[k];
    }).filter(v => v !== null);
  }

  doSelectNext(c) {
    const options = this.getOptions();
    if (!options.length) {
      return;
    }

    const currentIdx = options.indexOf(c);
    let focusIdx;
    if (currentIdx === (options.length - 1) || currentIdx === -1) {
      focusIdx = 0;
    } else {
      focusIdx = currentIdx + 1;
    }

    options[focusIdx].focus();
  }

  doSelectPrev(c) {
    const options = this.getOptions();
    if (!options.length) {
      return;
    }

    const currentIdx = options.indexOf(c);
    let focusIdx;
    if (currentIdx === 0 || currentIdx === -1) {
      focusIdx = options.length - 1;
    } else {
      focusIdx = currentIdx - 1;
    }

    options[focusIdx].focus();
  }

  closeMenu() {
    if (this.state.expanded) {
      this.toggleExpanded();
    }
  }

  toggleExpanded() {
    this.setState({
      expanded: !this.state.expanded
    });
  }

  onClickHeader = (ev) => {
    this.toggleExpanded();
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

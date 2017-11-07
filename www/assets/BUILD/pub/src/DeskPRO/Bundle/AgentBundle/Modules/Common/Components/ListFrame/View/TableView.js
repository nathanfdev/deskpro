import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { constants } from '../../../../../../../Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';

export class Table extends Component {

  static propTypes = {
    children:       PropTypes.any.isRequired,
    tableClassName: PropTypes.string
  };

  render() {
    return (
      <div className="dpmw--items-table-list">
        <table className={this.props.tableClassName}>
          {this.props.children}
        </table>
      </div>
    );
  }
}

export class TableGroupDivider extends Component {

  static propTypes = {
    title:   PropTypes.any,
    columns: PropTypes.number
  };

  static defaultProps = {
    columns: 1000
  };

  render() {
    const { title, columns } = this.props;

    return (
      <tr className="divider">
        <td colSpan={columns}>
          <hr />
          {title}
        </td>
      </tr>
    );
  }
}

export class Th extends Component {

  static propTypes = {
    title:     PropTypes.string,
    sort:      PropTypes.string,
    orderBy:   PropTypes.string,
    orderDir:  PropTypes.string,
    onChange:  PropTypes.func,
    visible:   PropTypes.bool,
    className: PropTypes.string
  };

  onChange = () => {
    const { sort, orderBy, orderDir, onChange } = this.props;

    if (!onChange) {
      return;
    }

    const newOrderDir = sort === orderBy && orderDir === constants.ORDER_DESC
      ? constants.ORDER_ASC
      : constants.ORDER_DESC;

    onChange(sort, newOrderDir);
  };

  render() {
    const { sort, orderBy, orderDir, title, visible = true, onChange, className } = this.props;
    const classes = {
      'fa-caret-down': orderDir === constants.ORDER_DESC,
      'fa-caret-up':   orderDir === constants.ORDER_ASC
    };

    return (
      <th onClick={this.onChange} className={classNames(className, { hidden: !visible }, { sortable: !!onChange })}>
        {title}
        {sort && orderBy === sort && <span><i className={classNames('fa', classes)} /></span>}
      </th>
    );
  }
}

export class Td extends Component {

  static propTypes = {
    visible:   PropTypes.bool,
    className: PropTypes.string,
    children:  PropTypes.any,
    style:     PropTypes.object
  };

  render() {
    const { visible = true, children, className, style } = this.props;

    return (
      <td className={classNames({ hidden: !visible }, className)} style={style}>
        {children}
      </td>
    );
  }
}

export class TdId extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <Td className="id-col" {...this.props}>
        <span className="dpw--item-id">#{this.props.children}</span>
      </Td>
    );
  }
}

export class TdTitle extends Component {

  render() {
    return <Td className="subject-col sortable" {...this.props} />;
  }
}

export class PersonInTable extends Component {

  static propTypes = {
    person: PropTypes.object,
    email:  PropTypes.string
  };

  render() {
    const { person } = this.props;

    if (!person) {
      return null;
    }

    return (
      <div className="user">
        <span className="dpw--avatar-face" style={{ backgroundImage: 'url(../img/avatars/avatar1.png)' }} />
        <span className="agent-name">{person.get('name')} {person.get('primary_email')}</span>
      </div>
    );
  }
}

export class TableCheckbox extends Component {

  static propTypes = {
    onClick:  PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    const divClasses      = classNames('dpwd-navigation-top-row-mass-action-checkbox', { active: this.props.selected });
    const checkboxClasses = classNames('fa', { 'fa-check': this.props.selected });
    const style           = { position: 'relative', border: 'none', margin: 0, width: 'auto', height: 'auto' };

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container" style={style}>
        <div className={divClasses} onClick={this.props.onClick} style={{ margin: 0, padding: 0 }}>
          <i className={checkboxClasses} />
        </div>
      </div>
    );
  }
}

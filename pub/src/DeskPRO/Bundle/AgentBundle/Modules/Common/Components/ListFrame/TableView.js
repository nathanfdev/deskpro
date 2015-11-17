import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';

export class Table extends Component {

  static propTypes = {
    children: PropTypes.any.isRequired
  };

  render() {
    return (
      <div className="dpmw--items-table-list">
        <table className="condensed-task-list">
          {this.props.children}
        </table>
      </div>
    );
  }
}

export class TableGroup extends Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <tbody>
        <tr className="divider">
          <td colSpan="1000">
            <hr />
            {title}
          </td>
        </tr>

        {children}
      </tbody>
    );
  }
}

export class Th extends Component {

  static propTypes = {
    title: PropTypes.string,
    sort: PropTypes.string,
    currentSort: PropTypes.string,
    currentOrder: PropTypes.string,
    onChange: PropTypes.func,
    visible: PropTypes.bool
  };

  onChange = () => {
    const { sort, currentSort, currentOrder, onChange } = this.props;
    if (!onChange) {
      return;
    }

    const newOrder = sort === currentSort && currentOrder === constants.ORDER_DESC
      ? constants.ORDER_ASC
      : constants.ORDER_DESC;

    onChange(sort, newOrder);
  };

  render() {
    const { sort, currentSort, currentOrder, title, visible = true, onChange } = this.props;

    return (
      <th onClick={this.onChange}
          className={classNames(
            {'hidden': !visible},
            {'sortable': !!onChange}
          )}>

        {title}
        {sort && currentSort === sort &&
          <span>
            <i className={classNames('fa', {
              'fa-caret-down': currentOrder === constants.ORDER_DESC,
              'fa-caret-up': currentOrder === constants.ORDER_ASC
            })}/>
          </span>
        }
      </th>
    );
  }
}

export class Td extends Component {

  static propTypes = {
    visible: PropTypes.bool,
    className: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { visible = true, children, className } = this.props;

    return (
      <td className={classNames({'hidden': !visible}, className)}>
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
    return <Td className="item-title" {...this.props} />;
  }
}

export class PersonInTable extends Component {

  static propTypes = {
    person: PropTypes.object.isRequired,
    email: PropTypes.string
  };

  render() {
    const { person } = this.props;
    return (
      <div className="user">
        <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar1.png)'}}></span>
        <span className="agent-name">{person.get('name')} {person.get('primary_email')}</span>
      </div>
    );
  }
}

export class TableCheckbox extends Component {

  static propTypes = {
    onClick: PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    var divClasses = classNames('dpwd-navigation-top-row-mass-action-checkbox', {active: this.props.selected});
    var checkboxClasses = classNames('fa', {'fa-check': this.props.selected});

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container"
           style={{position: 'relative', border: 'none', margin: 0, width: 'auto', height: 'auto'}}>
        <div className={divClasses} onClick={this.props.onClick} style={{margin: 0, padding: 0}}>
          <i className={checkboxClasses}></i>
        </div>
      </div>
    );
  }
}
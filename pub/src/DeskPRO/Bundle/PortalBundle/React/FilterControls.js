import React from "react"
import _ from "lodash"

class Tab extends React.Component {
  click(e) {
    e.preventDefault();
    this.props.setStatus(this.props.id);

  }
  render() {
    return (
      <li>
        <a href={'/feedback/browse/' + this.props.id}
           className={this.props.active ? "active" : ""}
           onClick={this.click.bind(this)}
          >
          {this.props.label}
          {/* TODO implement types <span><i className="fa fa-caret-down"></i></span>*/}
        </a>
    </li>
    );
  }
}

class FilterTabs extends React.Component {
  render() {
    return (
      <ul className="flat-tabs">
        {_.map(this.props.available, (status, status_id) => {
          return (<Tab key={status_id} label={status} id={status_id} active={this.props.selected == status_id} setStatus={this.props.setStatus} />);
        })}
      </ul>
    );
  }
}

class TypeSlider extends React.Component {
  click(e) {
    e.preventDefault();
    this.props.toggleType(this.props.id);
  }
  render() {
    return (
      <li>
        <div className="slider-panel">
          <a href={'/feedback/browse/type-' + this.props.id}
             className={this.props.active ? "slider" : "slider off"}
             onClick={this.click.bind(this)}>
            <span className="slider-status">on</span>
            <span className="slider-icon"><i className="fa fa-check"></i></span>
          </a>
            <span className="slider-label">
              {this.props.label}
            </span>
          {/**<span className="slider-options">
           <i className="fa fa-caret-down"></i>
           </span>**/}
        </div>
      </li>
    );
  }
}

class FilterTypes extends React.Component {
  render() {
    return (
      <ul className="slider-list">
        {_.map(this.props.available, (type, type_id) => {
          return (
            <TypeSlider key={type_id} label={type} id={type_id} active={_.includes(this.props.selected, _.parseInt(type_id))} toggleType={this.props.toggleType}  />
          );
        })}
      </ul>
    );
  }
}

export default class FilterControls extends React.Component {
  constructor(props) {
    super(props);
    this.state = props.filter_data;
  }
  generateUrlFromState() {
    // do a window.BASE_URL type thing
    let url = '/feedback/browse/';
    let filter = this.state.filter;

    url += filter.status;

    if (filter.types.length > 0) {
      url += '/type-';
      url += filter.types.join(',');
    }

    return url;
  }
  setStatus(status_id) {
    if (this.state.filter.status != status_id) {
      this.state.filter.status = status_id;
      this.setState(this.state);
    }
  }
  toggleType(type_id) {
    type_id = _.parseInt(type_id);
    if (_.includes(this.state.filter.types, type_id)) {
      this.state.filter.types = _.filter(this.state.filter.types, (n) => {
        return n != type_id;
      });
      this.setState(this.state);
    } else {
      this.state.filter.types.push(type_id);
      this.setState(this.state);
    }
  }
  render() {
    return (

      <div className="feedback-filter">
        <p>STATE: {JSON.stringify(this.state.filter)}</p>
        <p>URL: {this.generateUrlFromState()}</p>
        <FilterTabs available={this.state.available.status} selected={this.state.filter.status} setStatus={this.setStatus.bind(this)} />

        {/**<div className="table-meta">
          <div className="table-controls">
            <a href="#" className="column-control sort"><span>Sort</span></a>
            <a href="#" className="expand-control"><i className="fa fa-caret-right"></i></a>
          </div>
        </div>**/}

        <FilterTypes available={this.state.available.types} selected={this.state.filter.types} toggleType={this.toggleType.bind(this)} />
      </div>
    );
  }
}

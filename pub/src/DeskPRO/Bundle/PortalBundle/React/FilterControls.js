import React from "react"
import _ from "lodash"

class StatusCategory extends React.Component {
  clicked(e) {
    e.preventDefault();
    this.props.setStatusCategory(this.props.cat.id);
  }
  render() {
    return (
        <a style={this.props.isActive ? { fontWeight: 'bold'} : {}} onClick={this.clicked.bind(this)}>
          {this.props.cat.title}
        </a>
    );
  }
}

class Tab extends React.Component {
  clickTab(e) {
    e.preventDefault();
    this.props.setStatus(this.props.id);
  }
  isActiveStatusCategory(cat_id) {
    return _.includes(this.props.activeCategories, cat_id);
  }
  render() {
    return (
      <li>
        <div className="quick-jump">
          <a href={'/feedback/browse/' + this.props.id}
             className={this.props.active ? "active" : ""}
             onClick={this.clickTab.bind(this)}
            >
            {this.props.label}
            {/* TODO implement types <span><i className="fa fa-caret-down"></i></span>*/}
          </a>

          <div className="dropdown-content">
            <ul>
            {_.map(this.props.available.getStatusCategoriesForStatus(this.props.id), (cat) => {
              return (
                <li>
                <StatusCategory
                  key={cat.id}
                  cat={cat}
                  isActive={this.isActiveStatusCategory(cat.id)}
                  setStatusCategory={this.props.setStatusCategory}
                  />
                  </li>
              );
            })}
            </ul>
          </div></div>
    </li>
    );
  }
}

class FilterTabs extends React.Component {
  render() {
    return (
      <ul className="flat-tabs">
        {_.map(this.props.available.status, (status, status_id) => {
          return (
            <Tab
              key={status_id}
              label={status}
              id={status_id}
              active={this.props.filter.getStatus() == status_id}
              activeCategories={this.props.filter.status_categories}
              setStatus={this.props.setStatus}
              available={this.props.available}
              setStatusCategory={this.props.setStatusCategory}
            />
          );
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
            <span className="slider-status">{this.props.active ? "on" : "off"}</span>
            <span className="slider-icon"><i className="fa fa-check"></i></span>
          </a>
          <span className="slider-label" onClick={this.click.bind(this)}>
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
  updateFilter(filter) {
    this.props.updateFilter(filter);
  }
  setStatus(status_id) {
    let filter = this.props.filterModel;
    filter.setStatus(status_id);
    this.updateFilter(filter);
  }
  setStatusCategory(status_category_id) {
    let filter = this.props.filterModel;
    filter.toggleStatusCategory(status_category_id);
    this.updateFilter(filter);
  }
  toggleType(type_id) {
    let filter = this.props.filterModel;
    filter.toggleType(type_id);
    this.updateFilter(filter);
  }
  render() {
    return (
      <div className="feedback-filter">
        <p>STATE: {JSON.stringify(this.props.filterModel)}</p>
        <p>URL: {this.props.filterModel.createUrl().getUrl()}</p>
        <FilterTabs
          available={this.props.available}
          filter={this.props.filterModel}
          setStatus={this.setStatus.bind(this)}
          setStatusCategory={this.setStatusCategory.bind(this)}
          />

        {/**<div className="table-meta">
          <div className="table-controls">
            <a href="#" className="column-control sort"><span>Sort</span></a>
            <a href="#" className="expand-control"><i className="fa fa-caret-right"></i></a>
          </div>
        </div>**/}

        <FilterTypes available={this.props.available.types} selected={this.props.filterModel.types} toggleType={this.toggleType.bind(this)} />
      </div>
    );
  }
}

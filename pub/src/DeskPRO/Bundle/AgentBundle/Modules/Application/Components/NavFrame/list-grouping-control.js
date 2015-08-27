import React from 'react';


export class ListGroupingControl extends React.Component {

  render() {
    const {title, onChange, options, visible} = this.props;
    const className = visible ? 'sidebar-hover show' : 'sidebar-hover hide';

    return (
      <section className={className}>
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tag"></i>
            <span>&nbsp;</span>
            <span>{title}</span>
          </div>
          <form>
            <p>
              <label>Grouping Options:</label>
              <select onChange={onChange}>
                {options.map(option => <option key={option.value} value={option.value}>{option.label}</option>)}
              </select>
            </p>
          </form>
        </div>
      </section>
    );
  }

  shouldComponentUpdate(nextProps) {
    return nextProps.visible !== this.props.visible;
  }
}
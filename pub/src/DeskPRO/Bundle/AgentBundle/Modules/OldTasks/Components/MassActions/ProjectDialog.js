import React from 'react';

export default class ProjectDialog extends React.Component {
  static propTypes = {
    projects: React.PropTypes.object,
    massActionable: React.PropTypes.object,
    setMassActionProject: React.PropTypes.func
  }

  constructor(props) {
    super(props);

    this.state = {
      projects: props.projects,
      filterValue: null
    };
  }

  filterProjects(event) {
    const filter = event.target.value;

    if (filter) {
      this.setState({
        projects: this.props.projects.filter((project) => {
          return project.get('title').toLowerCase().indexOf(filter.toLowerCase()) > -1;
        }),
        filterValue: filter
      });
    } else {
      this.setState({
        projects: this.props.projects,
        filterValue: null
      });
    }
  }

  clearFilter() {
    this.setState({
      projects: this.props.projects,
      filterValue: null
    });
  }

  render() {
    return (
      <div className="dpw-navigation-dropdown-panel" style={{width: '240px'}}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw-departments-long-list">
                <div className="dpw-quick-filter">
                  <div className="dpw-quick-filter-container">
                    <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                    <input type="text" placeholder="Quick Filter Projects" value={this.state.filterValue} onChange={this.filterProjects.bind(this)} />
                    <span className="dpw-quick-filter-clear-link" onClick={this.clearFilter.bind(this)}><i className="fa fa-times-circle" /></span>
                  </div>
                </div>

                <div className="dpw--popup-item-collection">
                  <ul>
                    {this.state.projects ? this.state.projects.map((project) => {
                      let className = 'dpw--popup-item-box';
                      if (this.props.massActionable && this.props.massActionable.project && project.get('id') === this.props.massActionable.project) {
                        className += ' selected';
                      }

                      return (<li key={project.get('id')} onClick={this.props.setMassActionProject.bind(this, project.get('id'))}>
                                <div className={className}>
                                  <span className="dpw-popup-item-collection-name">{project.get('title')}</span>
                                </div>
                              </li>);
                    }) : ''}
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

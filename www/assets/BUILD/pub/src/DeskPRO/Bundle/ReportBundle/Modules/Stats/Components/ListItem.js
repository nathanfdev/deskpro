import React, { PropTypes } from 'react';

class ListItem extends React.Component {

  static propTypes = {
    report:        PropTypes.object.isRequired,
    onReportClick: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.onClick = this.onClick.bind(this);
  }

  onClick(event) {
    event.preventDefault();
    this.props.onReportClick(this.props.report);
  }

  render() {
    const { report } = this.props;

    return (
      <li onClick={this.onClick}>
        <span className="mark" />
        <h1>
          <a>{ report.get('title') }</a>
        </h1>
        { report.get('labels').size > 0 ?
          <p>
            {report.get('labels').map(
              (label, index) =>
                <span key={index} className="stat-label"><i className="fa fa-tag" /> { label }</span>
            )}
          </p>
          : null
        }
      </li>
    );
  }
}

export default ListItem;

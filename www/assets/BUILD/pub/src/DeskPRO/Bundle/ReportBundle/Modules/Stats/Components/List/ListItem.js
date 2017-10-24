import React, { PropTypes } from 'react';
import classNames from 'classnames';

class ListItem extends React.Component {

  static propTypes = {
    report:        PropTypes.object.isRequired,
    onReportClick: PropTypes.func.isRequired,
    onLabelClick:  PropTypes.func.isRequired,
    labels:        PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.onClick = this.onClick.bind(this);
  }

  onClick(event) {
    event.preventDefault();
    this.props.onReportClick(this.props.report);
  }

  isLabelActive(label) {
    const activeLabels = this.props.labels.filter(value => value.get('active')).map(value => value.get('label')).toJS();
    return activeLabels.indexOf(label) >= 0;
  }

  render() {
    const { report } = this.props;

    return (
      <li onClick={this.onClick}>
        <span className="mark" />
        <h1>
          <a>{ report.get('title') }</a>
        </h1>
        { report.get('labels').size > 0 || !report.get('is_custom') ?
          <p>
            {report.get('labels').map(
              (label, index) =>
                <span
                  onClick={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    this.props.onLabelClick(label);
                  }}
                  key={index}
                  className={classNames('stat-label', { active: this.isLabelActive(label) })}
                >
                  <i className="fa fa-tag" /> { label }
                </span>
            )}
            { !report.get('is_custom')
              ? <span className="stat-label active"><i className="fa fa-tag" /> Built-in report</span>
              : null
            }
          </p>
          : null
        }
      </li>
    );
  }
}

export default ListItem;

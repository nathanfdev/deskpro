import React, { PropTypes } from 'react';

class Run extends React.Component {

  static propTypes = {
    report: PropTypes.object,
  };

  render() {
    const { report } = this.props;
    return (
      <div className="stat-large-preview-wrapper">
        <span dangerouslySetInnerHTML={{ __html: report.get('rendered_result') }} />
      </div>
    );
  }
}

export default Run;

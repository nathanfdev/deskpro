import PropTypes from 'prop-types';
import React from 'react';
import PortalSimpleSelectBox from 'DeskPRO/Bundle/PortalBundle/React/Form/PortalSimpleSelectBox';

class GuideSelector extends React.Component {
  static propTypes = {
    guideSlug:   PropTypes.string,
    selectGuide: PropTypes.func
  };

  constructor(props) {
    super(props);
    let guides = [];
    if (window.guides) {
      guides = JSON.parse(window.guides);
    }
    this.state = {
      guides
    };
  }

  onClickGuide = (guide) => {
    this.props.selectGuide(guide);
  };

  render() {
    if (this.state.guides.length === 1) {
      return (
        <div className="guide">{this.state.guides[0].title}</div>
      );
    }

    const activeGuide = this.state.guides.filter(g => g.slug === this.props.guideSlug)[0];

    return (
      <div className="current-guide">
        <PortalSimpleSelectBox
          options={this.state.guides}
          value={activeGuide}
          onChange={this.onClickGuide}
        />
        { activeGuide.guide_pdf ?
          <a className="guide-pdf" href={activeGuide.guide_pdf} target="_blank" rel="noopener noreferrer">
            <span>Download PDF</span>
            <i className="fa fa-file-pdf-o" />
          </a> :
          ''
        }
      </div>
    );
  }
}
export default GuideSelector;

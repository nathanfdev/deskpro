import React, { PropTypes } from 'react';
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
      <PortalSimpleSelectBox
        options={this.state.guides}
        value={activeGuide}
        onChange={this.onClickGuide}
      />
    );
  }
}
export default GuideSelector;

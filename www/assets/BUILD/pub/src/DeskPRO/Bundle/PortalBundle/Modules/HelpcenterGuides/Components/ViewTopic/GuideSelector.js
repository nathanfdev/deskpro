import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';

class GuideSelector extends React.Component {
  static propTypes = {
    guideSlug:   PropTypes.string,
    selectGuide: PropTypes.func,
    fixed:       PropTypes.bool,
  };

  constructor(props) {
    super(props);
    let guides = [];
    if (window.guides) {
      guides = JSON.parse(window.guides);
    }
    if (!Array.isArray(guides)) {
      guides = Object.values(guides);
    }
    this.state = {
      guides
    };
  }

  onClickGuide = (e, guide) => {
    e.preventDefault();
    this.props.selectGuide(guide);
  };

  renderGuide = (guide, activeGuide, baseUrl) => (
    <div className={classNames('dp-po-guides-carousel-item', { active: guide.id === activeGuide.id })} key={guide.id}>
      <div className="dp-po-guides-carousel-content">
        <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-carousel-link" onClick={e => this.onClickGuide(e, guide)}>
          <IconRenderer object={guide} default={<i className="dp-po-icon fal fa-user-headset" />} /> {guide.title}
        </a>
      </div>
    </div>
    );

  render() {
    const { guides } = this.state;
    const { fixed } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const activeGuide = guides.filter(g => g.slug === this.props.guideSlug)[0];

    return (
      <div className={classNames('dp-po-guides-carousel', { fixed })}>
        <div className="dp-po-guides-carousel-list">
          {guides.map(guide => this.renderGuide(guide, activeGuide, baseUrl))}
        </div>
        <div className="dp-po-carousel-shadow" />
      </div>
    );
  }
}
export default GuideSelector;

import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

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
    if (!Array.isArray(guides)) {
      guides = Object.values(guides);
    }
    this.state = {
      guides
    };
  }

  onClickGuide = (guide) => {
    this.props.selectGuide(guide);
  };

  render() {
    const { guides } = this.state;
    const activeGuide = guides.filter(g => g.slug === this.props.guideSlug)[0];

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    return (
      <div className="dp-po-guides-carousel">
        <div className="dp-po-guides-carousel-list">
          {guides.map(guide => (
            <div className={classNames('dp-po-guides-carousel-item', { active: guide.id === activeGuide.id })}>
              <div className="dp-po-guides-carousel-content">
                <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-carousel-link">
                  <i className="dp-po-icon fal fa-user-headset" /> {guide.title}
                </a>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }
}
export default GuideSelector;

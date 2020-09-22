import PropTypes from 'prop-types';
import React, { Fragment } from 'react';
import classNames from 'classnames';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';
import GuideDropDown from './GuideDropDown';

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
    let mode = 'tabs';
    if (guides.length > 5 || (guides.length === 5 && window.innerWidth < 1190) || (guides.length === 4 && window.innerWidth < 976) || window.innerWidth < 765) {
      mode = 'dropdown';
    }
    this.state = {
      guides,
      mode,
    };
    window.addEventListener('resize', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.switchMode();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
  }

  onClickGuide = (e, guide) => {
    e.preventDefault();
    this.props.selectGuide(guide);
  };

  switchMode = () => {
    const { guides } = this.state;
    if (guides.length > 5 || (guides.length === 5 && window.innerWidth < 1190) || (guides.length === 4 && window.innerWidth < 976) || window.innerWidth < 765) {
      this.setState({
        mode: 'dropdown'
      });
    } else {
      this.setState({
        mode: 'tabs'
      });
    }
  }

  renderGuide = (guide, activeGuide, baseUrl) => (
    <div className={classNames('dp-po-guides-tabs-item', { active: guide.id === activeGuide.id })} key={guide.id}>
      <div className="dp-po-guides-tabs-content">
        <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-tabs-link" onClick={e => this.onClickGuide(e, guide)} title={guide.title}>
          <IconRenderer object={guide} key={guide.icon_property ? guide.icon_property.urn_path : 'fa-user-headset'} figureStyle={{ backgroundColor: guide.color ? `#${guide.color}` : 'var(--warning)' }} className="" default={<i className="fal fa-user-headset" />} /> {guide.title}
        </a>
      </div>
    </div>
    );

  render() {
    const { guides, mode } = this.state;
    const { fixed, selectGuide } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const reverseGuides = [...guides].reverse();

    const activeGuide = guides.filter(g => g.slug === this.props.guideSlug)[0];

    return (
      <Fragment>
        <div className={classNames('dp-po-guides-tabs', { fixed })} style={{ display: mode === 'tabs' ? 'block' : 'none' }}>
          <div className="dp-po-guides-tabs-list">
            {reverseGuides.map(guide => this.renderGuide(guide, activeGuide, baseUrl))}
          </div>
        </div>
        <GuideDropDown activeGuide={activeGuide} guides={guides} selectGuide={selectGuide} style={{ display: mode === 'dropdown' ? 'block' : 'none' }} />
      </Fragment>
    );
  }
}
export default GuideSelector;

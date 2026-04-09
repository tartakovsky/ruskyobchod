import { a1 as getMiGlobal, a8 as _export_sfc, o as openBlock, c as createElementBlock, D as normalizeStyle, E as normalizeClass, z as createCommentVNode, M as computed, $ as __, l as ref, aO as getUpgradeUrl } from "./TheAppHeader-CnbU8fIT.js";
function useIcon() {
  function getIcon(iconPath) {
    if (!iconPath || typeof iconPath !== "string") {
      console.warn("[useIcon] Invalid iconPath:", iconPath);
      return null;
    }
    {
      const assetsUrl = getMiGlobal("assets_url", "");
      if (!assetsUrl) {
        console.warn("[useIcon] assets_url not found in global config");
        return null;
      }
      return `${assetsUrl}/icons/${iconPath}`;
    }
  }
  function getTemplateIcon(templateName) {
    return getIcon(`templates/${"exactmetrics"}/${templateName}.svg`);
  }
  return {
    getIcon,
    getTemplateIcon
  };
}
const _hoisted_1 = ["aria-label"];
const _hoisted_2 = ["src", "alt", "aria-label"];
const _sfc_main = {
  __name: "Icon",
  props: {
    name: { type: String, required: true },
    size: { type: [Number, String], default: 48 },
    width: { type: [Number, String], default: null },
    height: { type: [Number, String], default: null },
    color: { type: String, default: "currentColor" },
    title: { type: String, default: "" }
  },
  setup(__props) {
    const props = __props;
    const isDashicon = computed(() => props.name.startsWith("dashicons-"));
    const { getIcon, getTemplateIcon } = useIcon();
    const iconUrl = computed(() => {
      if (isDashicon.value) return null;
      if (!props.name) {
        console.warn("[Icon] name prop is required");
        return null;
      }
      if (props.name.startsWith("templates/")) {
        const parts = props.name.split("/");
        const templateName = parts[parts.length - 1].replace(".svg", "");
        return getTemplateIcon(templateName);
      }
      const iconPath = props.name.endsWith(".svg") ? props.name : `${props.name}.svg`;
      return getIcon(iconPath);
    });
    const styleVars = computed(() => {
      const width = props.width || props.size;
      const height = props.height || props.size;
      return {
        "--mi-icon-width": typeof width === "number" ? `${width}px` : String(width),
        "--mi-icon-height": typeof height === "number" ? `${height}px` : String(height),
        "--mi-icon-color": props.color
      };
    });
    const dashiconStyles = computed(() => {
      const size = props.width || props.height || props.size;
      const sizeValue = typeof size === "number" ? `${size}px` : String(size);
      const styles = {
        fontSize: sizeValue,
        width: sizeValue,
        height: sizeValue,
        lineHeight: sizeValue
      };
      if (props.color !== "inherit") {
        styles.color = props.color;
      }
      return styles;
    });
    return (_ctx, _cache) => {
      return isDashicon.value ? (openBlock(), createElementBlock("span", {
        key: 0,
        class: normalizeClass(["dashicons", props.name]),
        style: normalizeStyle(dashiconStyles.value),
        role: "img",
        "aria-label": __props.title || void 0
      }, null, 14, _hoisted_1)) : iconUrl.value ? (openBlock(), createElementBlock("img", {
        key: 1,
        src: iconUrl.value,
        style: normalizeStyle(styleVars.value),
        class: "mi-icon",
        role: "img",
        alt: __props.title || props.name,
        "aria-label": __props.title || void 0
      }, null, 12, _hoisted_2)) : createCommentVNode("", true);
    };
  }
};
const Icon = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-a843a786"]]);
function useUpsellContent() {
  function getUpsellContent(feature) {
    const upsellTexts = {
      "custom-dashboard": {
        mainHeading: __("Analytics Dashboard", "google-analytics-dashboard-for-wp"),
        title: __(
          "Create custom analytics dashboards with drag and drop simplicity.",
          "google-analytics-dashboard-for-wp"
        ),
        features: [
          __("Highlight your businesses most important metrics", "google-analytics-dashboard-for-wp"),
          __("Create multiple views to group insights together", "google-analytics-dashboard-for-wp"),
          __("Easily view historical data and discover trends", "google-analytics-dashboard-for-wp"),
          __("Use bar charts, line graphs, and scorecards", "google-analytics-dashboard-for-wp"),
          __(
            "Works automatically with all eCommerce, forms, and custom dimensions",
            "google-analytics-dashboard-for-wp"
          ),
          __("Share with your team or export as beautiful PDFs", "google-analytics-dashboard-for-wp")
        ],
        buttonText: {
          Lite: __("Upgrade to Pro", "google-analytics-dashboard-for-wp"),
          Plus: __("Upgrade to Pro", "google-analytics-dashboard-for-wp")
        },
        learnMoreUrl: "https://www.exactmetrics.com/features/custom-dashboard/",
        sampleDataAvailable: true,
        requiredLicense: "Pro"
      },
      ecommerce: {
        mainHeading: __("eCommerce Report", "google-analytics-dashboard-for-wp"),
        title: __(
          "Increase Sales and Make More Money With Enhanced eCommerce Insights",
          "google-analytics-dashboard-for-wp"
        ),
        features: [
          __("10+ eCommerce Integrations", "google-analytics-dashboard-for-wp"),
          __("Average Order Value", "google-analytics-dashboard-for-wp"),
          __("Total Revenue", "google-analytics-dashboard-for-wp"),
          __("Sessions to Purchase", "google-analytics-dashboard-for-wp"),
          __("Top Conversion Sources", "google-analytics-dashboard-for-wp"),
          __("Top Products", "google-analytics-dashboard-for-wp"),
          __("Number of Transactions", "google-analytics-dashboard-for-wp"),
          __("Time to Purchase", "google-analytics-dashboard-for-wp")
        ],
        buttonText: {
          Lite: __("Upgrade to Plus", "google-analytics-dashboard-for-wp")
        },
        learnMoreUrl: "https://www.exactmetrics.com/addon/ecommerce/",
        sampleDataAvailable: true,
        requiredLicense: "Plus"
      },
      forms: {
        mainHeading: __("Forms Report", "google-analytics-dashboard-for-wp"),
        title: __(
          "Track Every Type of Web Form and Gain Visibility Into Your Customer Journey",
          "google-analytics-dashboard-for-wp"
        ),
        features: [
          __("Conversion Counts", "google-analytics-dashboard-for-wp"),
          __("Impression Counts", "google-analytics-dashboard-for-wp"),
          __("Conversion Rates", "google-analytics-dashboard-for-wp")
        ],
        buttonText: {
          Lite: __("Upgrade to Plus", "google-analytics-dashboard-for-wp")
        },
        learnMoreUrl: "https://www.exactmetrics.com/addon/forms/",
        sampleDataAvailable: true,
        requiredLicense: "Plus"
      },
      publisher: {
        mainHeading: __("Publishers Report", "google-analytics-dashboard-for-wp"),
        title: __(
          "Improve Your Conversion Rate With Insights Into Which Content Works Best",
          "google-analytics-dashboard-for-wp"
        ),
        features: [
          __("Top Landing Pages", "google-analytics-dashboard-for-wp"),
          __("Top Affilliate Links", "google-analytics-dashboard-for-wp"),
          __("Top Exit Pages", "google-analytics-dashboard-for-wp"),
          __("Top Download Links", "google-analytics-dashboard-for-wp"),
          __("Top Outbound Links", "google-analytics-dashboard-for-wp"),
          __("Scroll Depth", "google-analytics-dashboard-for-wp")
        ],
        buttonText: {
          Lite: __("Upgrade to Pro", "google-analytics-dashboard-for-wp"),
          Plus: __("Upgrade to Pro", "google-analytics-dashboard-for-wp")
        },
        learnMoreUrl: "https://www.exactmetrics.com/addon/publisher/",
        sampleDataAvailable: true,
        requiredLicense: "Pro"
      },
      dimensions: {
        mainHeading: __("Dimensions Report", "google-analytics-dashboard-for-wp"),
        title: __(
          "Increase Engagement and Unlock New Insights About Your Site",
          "google-analytics-dashboard-for-wp"
        ),
        features: [
          __("Author Tracking", "google-analytics-dashboard-for-wp"),
          __("User ID Tracking", "google-analytics-dashboard-for-wp"),
          __("Post Types", "google-analytics-dashboard-for-wp"),
          __("Tag Tracking", "google-analytics-dashboard-for-wp"),
          __("Categories", "google-analytics-dashboard-for-wp"),
          __("SEO Scores", "google-analytics-dashboard-for-wp"),
          __("Publish Times", "google-analytics-dashboard-for-wp"),
          __("Focus Keywords", "google-analytics-dashboard-for-wp")
        ],
        buttonText: {
          Lite: __("Upgrade to Plus", "google-analytics-dashboard-for-wp")
        },
        learnMoreUrl: "https://www.exactmetrics.com/addon/dimensions/",
        sampleDataAvailable: true,
        requiredLicense: "Plus"
      }
    };
    return upsellTexts[feature] || null;
  }
  return {
    getUpsellContent
  };
}
const sharedState = {};
function getSharedState(feature) {
  if (!sharedState[feature]) {
    sharedState[feature] = {
      isUpsellModalOpen: ref(false),
      isSampleMode: ref(false)
    };
  }
  return sharedState[feature];
}
function useFeatureGate(feature) {
  const { getUpsellContent } = useUpsellContent();
  const { isUpsellModalOpen, isSampleMode } = getSharedState(feature);
  const hasAccess = computed(() => {
    const license = getMiGlobal("license", {});
    const licenseType = (license.type || "").toLowerCase();
    return licenseType === "pro" || licenseType === "elite";
  });
  const upsellContent = computed(() => {
    return getUpsellContent(feature);
  });
  const minimumLicense = computed(() => {
    return __("Pro", "google-analytics-dashboard-for-wp");
  });
  const currentLicense = computed(() => {
    const license = getMiGlobal("license", {});
    const type = license.type || "";
    return type.charAt(0).toUpperCase() + type.slice(1) || __("Lite", "google-analytics-dashboard-for-wp");
  });
  const upgradeButtonText = computed(() => {
    if (!upsellContent.value) {
      return __("Upgrade Now", "google-analytics-dashboard-for-wp");
    }
    const buttonTextConfig = upsellContent.value.buttonText;
    if (typeof buttonTextConfig === "object") {
      return buttonTextConfig[currentLicense.value] || // translators: %s is the license level (e.g., "Pro", "Plus")
      __("Upgrade to %s", "google-analytics-dashboard-for-wp").replace(
        "%s",
        minimumLicense.value
      );
    }
    return buttonTextConfig || __("Upgrade to %s", "google-analytics-dashboard-for-wp").replace(
      "%s",
      minimumLicense.value
    );
  });
  const hasSampleData = computed(() => {
    return upsellContent.value?.sampleDataAvailable || false;
  });
  const openUpsellModal = () => {
    isUpsellModalOpen.value = true;
    isSampleMode.value = false;
  };
  const closeUpsellModal = () => {
    isUpsellModalOpen.value = false;
  };
  const enableSampleMode = () => {
    isSampleMode.value = true;
    closeUpsellModal();
  };
  const disableSampleMode = () => {
    isSampleMode.value = false;
  };
  const handleUpgrade = () => {
    const learnMoreUrl = upsellContent.value?.learnMoreUrl || "https://www.exactmetrics.com/pricing/";
    const upgradeUrl = getUpgradeUrl(
      "custom-dashboard-upsell",
      `upgrade-${feature}`,
      learnMoreUrl
    );
    window.open(upgradeUrl, "_blank");
  };
  const handleLearnMore = () => {
    const learnMoreUrl = upsellContent.value?.learnMoreUrl || "https://www.exactmetrics.com/";
    window.open(learnMoreUrl, "_blank");
  };
  const shouldBlurContent = computed(() => {
    return !hasAccess.value && !isSampleMode.value;
  });
  const shouldShowUpsell = computed(() => {
    return !hasAccess.value && isUpsellModalOpen.value && !isSampleMode.value;
  });
  return {
    // Access control
    hasAccess,
    minimumLicense,
    currentLicense,
    // Upsell content
    upsellContent,
    upgradeButtonText,
    hasSampleData,
    // Modal state
    isUpsellModalOpen,
    isSampleMode,
    shouldBlurContent,
    shouldShowUpsell,
    // Actions
    openUpsellModal,
    closeUpsellModal,
    enableSampleMode,
    disableSampleMode,
    handleUpgrade,
    handleLearnMore
  };
}
export {
  Icon as I,
  useFeatureGate as u
};

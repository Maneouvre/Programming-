export const pickupNodes = {
  nairobi: [
    { id: 'nbi-cbd', name: 'Nairobi CBD - Tom Mboya St Hub (G4S)', fee: 200, time: 'Mon-Sat 8AM - 6PM' },
    { id: 'nbi-west', name: 'Westlands - Sarit Centre Agent Desk', fee: 350, time: 'Mon-Sun 9AM - 8PM' }
  ],
  mombasa: [
    { id: 'msa-digo', name: 'Mombasa CBD - Digo Road Agent Node', fee: 400, time: 'Mon-Sat 8AM - 5PM' }
  ],
  kisumu: [
    { id: 'ksm-mega', name: 'Kisumu - Mega Plaza Main Hub (Wells Fargo)', fee: 450, time: 'Mon-Sat 8AM - 5PM' }
  ],
  uasin: [
    { id: 'eld-cbd', name: 'Eldoret CBD - Zion Mall Ground Station', fee: 450, time: 'Mon-Sat 8AM - 6PM' }
  ]
};

const PICKUP_STORAGE_KEY = 'pickup-selection';

export function getPickupStation(countyKey, stationId) {
  return (pickupNodes[countyKey] || []).find((station) => station.id === stationId) || null;
}

export function getPickupSelection() {
  const storedSelection = localStorage.getItem(PICKUP_STORAGE_KEY);
  if (!storedSelection) {
    return null;
  }

  try {
    const selection = JSON.parse(storedSelection);
    return getPickupStation(selection?.countyKey, selection?.stationId)
      ? selection
      : null;
  } catch (error) {
    console.error('Unable to read the saved pickup station.', error);
    return null;
  }
}

export function savePickupSelection(countyKey, stationId) {
  if (!getPickupStation(countyKey, stationId)) {
    throw new Error('Choose a valid pickup station before saving your selection.');
  }

  localStorage.setItem(PICKUP_STORAGE_KEY, JSON.stringify({ countyKey, stationId }));
}

export function clearPickupSelection() {
  localStorage.removeItem(PICKUP_STORAGE_KEY);
}
